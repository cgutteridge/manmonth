<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class Document extends Model
{

    // create an empty current revision. Documents must always have a current revision.
    // document must be saved before calling
    public function init() 
    {
        if( !$this->id ) { throw new Exception( "Save document before calling init()" ); }

        $rev = new DocumentRevision();
        $rev->document()->associate( $this );
        $rev->status = "current";
        $rev->save();
    }

    // This is a major workhorse function. It copies all the relevant data into a new revision. 
    // Rows get a new ID but maintain their 'sid' value and this is used for relationships.
    /**
     * @return DocumentRevision
     * @throws Exception
     */
    public function createDraftRevision()
    {
        // if there's already a draft throw an exception
        $draft = $this->draftRevision();
        if( $draft ) { throw new Exception( "Already a draft, can't make another one." ); }

        $current = $this->currentRevision();      
 
        $draft = $current->replicate();
        $draft->status = "draft";
        $draft->save();
       
        $partLists = array( 
            $current->reportTypes,
            $current->records,
            $current->recordTypes,
            $current->links,
            $current->linkTypes,
            $current->rules );
        // reports are a document part but belong to a single revision
      
        foreach( $partLists as $partList ) {
            foreach( $partList as $part ) {
                $newPart = $part->replicate(); 
                $newPart->documentRevision()->associate( $draft );
                $newPart->save();
            }
        }
 
        return $draft;
    }

    public function draftRevision()
    {
        return $this->revisions()->where( 'status', 'draft' )->first();
    }

    public function currentRevision()
    {
        // there must always be exactly one current revision so if there isn't
        // this throws an exception
        $first = $this->revisions()->where( 'status', 'current' )->first();
        if( !$first ) 
        {
            throw new Exception( "Document has no current revision. That should not happen, ever." );
        }
        return $first;
    }

    public function revisions()
    {
        return $this->hasMany('App\Models\DocumentRevision');
    }
}
