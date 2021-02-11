<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string name
 * @property DocumentRevision draftRevision
 * @property DocumentRevision latestRevision
 * @property DocumentRevision latestPublishedRevision
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

    /**
     * Return the draft revision, if there is one, otherwise null.
     * @return DocumentRevision|null
     */
    public function draftRevision()
    {
        return $this->hasOne(DocumentRevision::class)->where( 'status','draft')->orderBy('id');
    }

    /**
     * @return HasOne
     */
    public function latestRevision()
    {
        return $this->hasOne(DocumentRevision::class)->where( 'status','archive')->orderBy('id');
    }

    /**
     * @return null|DocumentRevision
     */
    public function latestPublishedRevision()
    {
        return $this->hasOne(DocumentRevision::class)
            ->where( 'status','archive')->where('published',1)->orderBy('id');
    }

    /*************************************
     * READ FUNCTIONS
     *************************************/



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
        $link->link_type_id = $this->id;
        $link->subject_id = $subject->id;
        $link->object_id = $object->id;

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
        if ($this->draftRevision) {
            throw new Exception("A draft revision of this document already exists.");
        }

        /** @var DocumentRevision $draft */
        $draft = $this->latestRevision->replicate();
        $draft->status = "draft";
        $draft->published = false;
        $draft->user()->associate($user);
        $draft->save();
        $partLists = array(
            $this->latestRevision->reportTypes,
            $this->latestRevision->records,
            $this->latestRevision->recordTypes,
            $this->latestRevision->links,
            $this->latestRevision->linkTypes,
            $this->latestRevision->rules);
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
