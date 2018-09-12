<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\DocumentPart;
use App\Models\DocumentRevision;
use App\Models\Link;
use App\Models\LinkType;
use App\Models\Permission;
use App\Models\Record;
use App\Models\RecordType;
use App\Models\ReportType;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use PDOException;
use PhpParser\Comment\Doc;
use Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
        /** @var Permission $permission */

        foreach ($this->getPermissions() as $permission) {
            if ($permission->global) {
                /**
                 * @param User $user
                 * @return boolean
                 */
                $fn =
                    function ($user) use ($permission) {
                        return $user->hasRole($permission->roles);
                    };
                $gate->define($permission->name, $fn);
            } else {
                /**
                 * @param User $user
                 * @param Document $document
                 * @return boolean
                 */
                $fn =
                    function ($user, $document) use ($permission) {
                        return $user->hasDocumentRole($permission->roles, $document);
                    };
                $gate->define($permission->name, $fn);
            }
        } // end loop over permissions

        // meta document permissions to call specific ones for an object

        /* GENERIC VIEW PERMISSION */

        // works out for this item ($docIndicator) what permission means we can see
        // it. Generally view-draft or whatnot.

        /**
         * @param User $user
         * @param Document|DocumentRevision|DocumentPart $docIndicator
         * @return boolean
         */
        $fn = function (User $user, $docIndicator) {
            if (is_a($docIndicator, Document::class)) {
                /** @var Document $document */
                $document = $docIndicator;
                // any of these permissions let you see the document.
                if ($user->can("view-published-latest", $document)) {
                    return true;
                };
                if ($user->can("view-published", $document)) {
                    return true;
                };
                if ($user->can("view-archive", $document)) {
                    return true;
                };
                return false;
            }

            if (is_a($docIndicator, DocumentRevision::class) || is_a($docIndicator, DocumentPart::class)) {
                // we treat a document part as if it were the document revision it's attached to
                // this might get more nuanced in a later version
                /** @var DocumentRevision $docRev */
                if (is_a($docIndicator, DocumentRevision::class)) {
                    $docRev = $docIndicator;
                } else {
                    // must be a doc part then. We can get the document revision from that.
                    /** @var DocumentPart $docPart */
                    $docPart = $docIndicator;
                    /** @var DocumentRevision $docRev */
                    $docRev = $docPart->documentRevision;
                }

                // the easy version is if the user can see any revision for of this type
                if ($user->can("view-" . $docRev->status, $docRev->document)) {
                    return true;
                }

                // otherwise we need to work out if they can see it as view-published or view-published-latest
                if ($docRev->published) {
                    if ($user->can("view-published", $docRev->document)) {
                        return true;
                    }
                    // last chance. If they can view the latest published, and this is the latest published revision
                    $latestPublishedRevision = $docRev->document->latestPublishedRevision();
                    if (isset($latestPublishedRevision) && $docRev->id = $latestPublishedRevision->id) {
                        return true;
                    }
                }
                return false;
            }

            // This should never happen!
            throw new \Exception("Tried to see if we can view a " . $docIndicator . " whiich wasn't any of the types we understand");
        };
        $gate->define('view', $fn);


        /* GENERIC EDIT PERMISSION */

        /**
         * The generic edit param can be tested on docparts only.
         * @param User $user
         * @param DocumentPart $documentPart
         * @return boolean
         */
        $fn = function (User $user, $documentPart) {
            $documentRevision = $documentPart->documentRevision;

            // only things in draft revisions can be edited.
            if ($documentRevision->status != "draft") {
                return false;
            }

            // only revisions created by the current user can be edited
            // (unless we have superusers later)
            if( $documentRevision->user_username != $user->username ) {
                return false;
            }

            if (
                is_a($documentPart, Link::class)
                || is_a($documentPart, Record::class)
            ) {
                return $user->can("edit-data", $documentRevision->document);
            } elseif (
                is_a($documentPart, LinkType::class)
                || is_a($documentPart, RecordType::class)
            ) {
                return $user->can("edit-schema", $documentRevision->document);
            } elseif (
                is_a($documentPart, ReportType::class)
                || is_a($documentPart, Rule::class)
            ) {
                return $user->can("edit-reports", $documentRevision->document);
            } else {
                return false;
            }
        };
        $gate->define('edit', $fn);


        /* GENERIC CREATE PERMISSION */

        /**
         * The generic append permission can be tested on docparts only.
         * This is disinct from generic edit as the logic to get from
         * an object to a permission is different. Code sits here so it
         * can do the draft-status-only check here rather than in the controllers.
         * @param User $user
         * @param DocumentPart|DocumentRevision $thing
         * @return boolean
         */
        $fn = function (User $user, $thing) {
            if (is_a($thing, DocumentRevision::class)) {
                $documentRevision = $thing;
            } else {
                $documentRevision = $thing->documentRevision;
            }
            // only things in draft revisions can be edited.
            if ($documentRevision->status != "draft") {
                return false;
            }

            // only revisions created by the current user can be edited
            // (unless we have superusers later)
            if( $documentRevision->user_username != $user->username ) {
                return false;
            }

            if (
                is_a($thing, LinkType::class)
                || is_a($thing, RecordType::class)
            ) {
                if ($thing->isProtected()) {
                    return false;
                }
                return $user->can("edit-data", $documentRevision->document);
            } elseif (is_a($thing, DocumentRevision::class)
            ) {
                return $user->can("edit-data", $documentRevision->document);
            } else {
                return false;
            }
        };
        $gate->define('create', $fn);

        /**
         * Can this user commit a specific revision?
         * @param User $user
         * @param DocumentRevision $documentRevision
         * @return boolean
         */
        $fn = function (User $user, DocumentRevision $documentRevision) {
            if( !$user->can( "commit", $documentRevision->document)) {
                return false;
            }
            // only revisions created by the current user can be committed
            // (unless we have superusers later)
            if( $documentRevision->user_username != $user->username ) {
                return false;
            }

            return true;
        };
        $gate->define('commit-revision', $fn);

    }

    protected
    function getPermissions()
    {
        try {
            // If the DB is not yet setup, we can't get any permissions!
            if (!Schema::hasTable('permissions')) {
                return new Collection();
            }
        } catch (PDOException $exception) {
            // If we can't even find out if there's a permissions table
            // then there's no permissions!
            return new Collection();
        }

        return Permission::with('roles')->get();
    }
}
