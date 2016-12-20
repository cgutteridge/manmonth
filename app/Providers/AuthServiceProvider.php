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

        /**
         * @param User $user
         * @param Document|DocumentRevision|DocumentPart $docIndicator
         * @return boolean
         */
        $fn = function ($user, $docIndicator) {
            if (is_a($docIndicator, Document::class)) {
                return $user->can("view-current", $docIndicator);
            } elseif (is_a($docIndicator, DocumentRevision::class)) {
                return $user->can("view-" . $docIndicator->status, $docIndicator->document);
            } else {
                // All DocumentParts have same view permission (currently) as
                // the documentRevision to which they are attached.
                $docRev = $docIndicator->documentRevision;
                return $user->can("view-" . $docRev->status, $docRev->document);
            }
        };
        $gate->define('view', $fn);


        /* GENERIC EDIT PERMISSION */

        /**
         * The generic edit param can be tested on docparts only.
         * @param User $user
         * @param DocumentPart $documentPart
         * @return boolean
         */
        $fn = function ($user, $documentPart) {
            $documentRevision = $documentPart->documentRevision;

            // only things in draft revisions can be edited.
            if ($documentRevision->status != "draft") {
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
        $fn = function ($user, $thing) {
            if (is_a($thing, DocumentRevision::class)) {
                $documentRevision = $thing;
            } else {
                $documentRevision = $thing->documentRevision;
            }
            // only things in draft revisions can be edited.
            if ($documentRevision->status != "draft") {
                return false;
            }
            if (
                is_a($thing, LinkType::class)
                || is_a($thing, RecordType::class)
                || is_a($thing, DocumentRevision::class)
            ) {
                return $user->can("edit-data", $documentRevision->document);
            } else {
                return false;
            }
        };
        $gate->define('create', $fn);
    }

    protected function getPermissions()
    {
        // If the DB is not yet setup, we can't get any permissions!
        if (!Schema::hasTable('permissions')) {
            return new Collection();
        }

        return Permission::with('roles')->get();
    }
}
