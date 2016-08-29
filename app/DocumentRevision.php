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

        /// all OK, let's do this thing

        $record_type = new RecordType();
        $record_type->documentRevision()->associate( $this );
        $record_type->name = $name;
        $record_type->data = json_encode( $data );
        $record_type->save();
        return $record_type;
    }

    public function newLinkType( $name, $domain, $range, $data ) 
    {
        $nameFormat = new \Structure\StringS();
        $nameFormat->setLength(2);

        $linkTypeDataFormat = new \Structure\ArrayS();
        $linkTypeDataFormat->setFormat( array(
            // currently linkTypes have no valid data. Later will have some stuff for export I expect.
        ));
        $linkTypeDataFormat->setCountStrict(true); // don't allow stray terms


        // validate name
        if( ! $nameFormat->check( $name, $fail ) ) {
            throw new Exception( "Error ".json_encode( $fail )." in name passed to newLinkType: ".json_encode( $name ) );
        }

        // validate data
        if( ! $linkTypeDataFormat->check( $data, $fail ) ) {
            throw new Exception( "Error ".json_encode( $fail )." in data passed to newLinkType: ".json_encode( $data ) );
        }



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


