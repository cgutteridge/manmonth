<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string name
 */
class Document extends MMModel
{
    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * The relationship to the revisions of this document.
     * @return HasMany
     */
    public function revisions()
    {
        return $this->hasMany(DocumentRevision::class);
    }

    /**
     * @return HasMany
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /*************************************
     * READ FUNCTIONS
     *************************************/

    /**
     * Return the draft revision, if there is one, otherwise null.
     * @return DocumentRevision|null
     */
    public function draftRevision()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->draftRevision";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->revisions()->where('status', 'draft')->first();
        }

        return MMModel::$cache[$relationCode];
    }

    /**
     * @return DocumentRevision
     * @throws Exception
     */
    public function latestRevision()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->latestRevision";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            MMModel::$cache[$relationCode] = $this->revisions()
                ->where('status', 'archive')
                ->orderBy('id', 'desc')
                ->first();
        }
        if (!MMModel::$cache[$relationCode]) {
            throw new Exception("Document has no latest revision. That should not happen, ever.");
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * @return null|DocumentRevision
     */
    public function latestPublishedRevision()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->latestPublishedRevision";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            MMModel::$cache[$relationCode] = $this->revisions()
                ->where('status', 'archive')
                ->where('published', true)
                ->orderBy('id', 'desc')
                ->first();
        }
        if (!MMModel::$cache[$relationCode]) {
            return null;
        }
        return MMModel::$cache[$relationCode];
    }

    /*************************************
     * ACTION FUNCTIONS
     *************************************/

    /**
     * @param $subject
     * @param $object
     * @return Link
     * @throws MMValidationException
     */
    public function createLink($subject, $object)
    {
        $this->validateLinkSubject($subject);
        $this->validateLinkObject($object);

        $link = new Link();
        $link->documentRevision()->associate($this->documentRevision);
        $link->link_type_sid = $this->sid;
        $link->subject_sid = $subject->sid;
        $link->object_sid = $object->sid;

        $this->validate();
        $link->save();

        return $link;
    }

    /**
     * Create an empty current revision. Documents must always have a current revision.
     * @throws Exception
     */
    public function init()
    {
        if (!$this->id) {
            throw new Exception("Save document before calling init()");
        }

        /* create a first revision in the archive */
        $rev = new DocumentRevision();
        $rev->document()->associate($this);
        $rev->status = "archive";
        $rev->save();

        /* add a config record type */

        $configType = $rev->createRecordType("config", [
            "label" => "Configuration",
            "data" => [
                "protected" => true,
                "fields" => [
                    ["name" => "comment", "label" => "Comment", "type" => "string", "required" => false, "protected" => true],
                ]],
            "title_script" => "'Configuration'"
        ]);

        $config = $configType->createRecord([
            "comment" => "Example comment."
        ]);
    }

    /**
     * This is a major workhorse function. It copies all the relevant data, from the
     * most recent item in the archive, into a new revision.
     * Rows get a new ID but maintain their 'sid' value and this is used for relationships.
     * @param User $user
     * @return DocumentRevision
     * @throws Exception
     */
    public function createDraftRevision(User $user)
    {
        //VERY BIG TODO
        die("this really needs updating before releasing to make the IDs work right");

        // if there's already a draft throw an exception
        $draft = $this->draftRevision();
        if ($draft) {
            throw new Exception("A draft revision of this document already exists.");
        }
        /** @var DocumentRevision $latest */
        $latest = $this->latestRevision();

        /** @var DocumentRevision $draft */
        $draft = $latest->replicate();
        $draft->status = "draft";
        $draft->published = false;
        $draft->user()->associate($user);
        $draft->save();
        $partLists = array(
            $latest->reportTypes,
            $latest->records,
            $latest->recordTypes,
            $latest->links,
            $latest->linkTypes,
            $latest->rules);
        // reports are a document part but belong to a single revision

        foreach ($partLists as $partList) {
            /** @var DocumentPart $part */
            foreach ($partList as $part) {
                /** @var DocumentPart $newPart */
                $newPart = $part->replicate();
                $newPart->documentRevision()->associate($draft);
                $newPart->save();
            }
        }

        return $draft;
    }


}
