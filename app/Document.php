<?php

namespace App;

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

    public function newDraftRevision()
    {
        // if there's already a draft throw an exception
        $draft = $this->draftRevision();
        if( $draft ) { throw new Exception( "Already a draft, can't make another one." ); }
       
        $draft = $this->currentRevision()->replicate();
        $draft->status = "draft";
        $draft->save();
 
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
        return $this->hasMany('App\DocumentRevision');
    }
}
