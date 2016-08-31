<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;

class DocumentRevision extends Model
{
    public function document()
    {
        return $this->belongsTo('App\Document');
    }

    public function recordTypes()
    {
        return $this->hasMany('App\RecordType');
    }

    public function records()
    {
        return $this->hasMany('App\Record');
    }

    public function linkTypes()
    {
        return $this->hasMany('App\LinkType');
    }

    public function links()
    {
        return $this->hasMany('App\Link');
    }

    public function rules()
    {
        return $this->hasMany('App\Rule');
    }



    public function publish() 
    {
        // can only publish if this is a draft
        if( $this->status != "draft" )
        {
            throw new Exception( "Can't publish a revision that is not a draft. status=".$this->status );
        }
        $oldRevision = $this->document->currentRevision();
        $oldRevision->status = "archive";
        $this->status = "current";
        $oldRevision->save();    
        $this->save();    
    }

    public function scrap() 
    {
        // can only publish if this is a draft
        if( $this->status != "draft" )
        {
            throw new Exception( "Can't scrap a revision that is not a draft. status=".$this->status );
        }
        $this->status = "scrap";
        $this->save();
    }


    public function newRecordType( $name, $data ) 
    {
        // these take exception if there's an issue
        RecordType::validateName( $name );
        RecordType::validateData( $data );

        // all OK, let's do this thing

        $record_type = new RecordType();
        $record_type->documentRevision()->associate( $this );
        $record_type->name = $name;
        $record_type->data = json_encode( $data );

        $record_type->save();
        return $record_type;
    }

    public function newLinkType( $name, $domain, $range, $data ) 
    {
        // these take exception if there's an issue
        LinkType::validateName( $name );
        LinkType::validateData( $data );
 
        if( !isset( $data["domain_min"] ) ) { $data["domain_min"]=0; }
        if( !isset( $data["domain_max"] ) ) { $data["domain_max"]=1; }
        if( !isset( $data["range_min"] ) ) { $data["range_min"]=0; }
        if( !isset( $data["range_max"] ) ) { $data["range_max"]=1; }

        // all OK, let's make this link type
        $record_type = new LinkType();
        $record_type->documentRevision()->associate( $this );
        $record_type->name = $name;
        $record_type->domain_sid = $domain->sid;
        $record_type->range_sid = $range->sid;
        $record_type->data = json_encode( $data );

        $record_type->save();
        return $record_type;
    }

}


