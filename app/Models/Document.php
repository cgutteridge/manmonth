<?php

namespace App\Models;

use Exception;

/**
 * @property int id
 * @property string name
 */
class Document extends MMModel
{

    /**
     * Create an empty current revision. Documents must always have a current revision.
     * @throws Exception
     */
    public function init()
    {
        if (!$this->id) {
            throw new Exception("Save document before calling init()");
        }

        /* create a basic current revision */
        $rev = new DocumentRevision();
        $rev->document()->associate($this);
        $rev->status = "current";
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
     * This is a major workhorse function. It copies all the relevant data into a new revision.
     * Rows get a new ID but maintain their 'sid' value and this is used for relationships.
     * @return DocumentRevision
     * @throws Exception
     */
    public function createDraftRevision()
    {
        // if there's already a draft throw an exception
        $draft = $this->draftRevision();
        if ($draft) {
            throw new Exception("Already a draft, can't make another one.");
        }

        /** @var DocumentRevision $current */
        $current = $this->currentRevision();

        /** @var DocumentRevision $draft */
        $draft = $current->replicate();
        $draft->status = "draft";
        $draft->save();

        $partLists = array(
            $current->reportTypes,
            $current->records,
            $current->recordTypes,
            $current->links,
            $current->linkTypes,
            $current->rules);
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

    /**
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
     * The relationship to the revisions of this document.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany('App\Models\DocumentRevision');
    }

    /**
     * @return DocumentRevision
     * @throws Exception
     */
    public function currentRevision()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->currentRevision";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            MMModel::$cache[$relationCode] = $this->revisions()->where('status', 'current')->first();
        }
        if (!MMModel::$cache[$relationCode]) {
            throw new Exception("Document has no current revision. That should not happen, ever.");
        }
        return MMModel::$cache[$relationCode];
    }


}
