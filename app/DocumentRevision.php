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
  
    public function recordTypeByName( $name ) {
        return $this->recordTypes->where( 'name', $name )->first();
    }

    public function linkTypeByName( $name ) {
        return $this->linkTypes->where( 'name', $name )->first();
    }

    // hard wired 'anchor' record type that everything else links to
    public function baseRecordType() {
        return $this->recordTypeByName( 'actor' );
    }

    // get the absract context for the route. Returns record & link types,
    // not specific records and links
    public function getAbstractContext( $route ) {
        $context = [];
        $baseRecordType = $this->baseRecordType();
        $context[$baseRecordType->name] = $baseRecordType;
        // add all the other objects in the route
        $iterativeRecordType = $baseRecordType;
        foreach( $route as $linkName ) {
            $fwd = true;
            if( substr( $linkName, 0, 1 ) == "^" ) {
                $linkName = substr( $linkName, 1 );
                $fwd = false;
            }
            $link = $this->linkTypeByName( $linkName );
            if( !$link ) {
                // not sure what type of exception to make this (Script?)
                throw new Exeception( "Unknown linkname in context '$linkName'" );
            }
            
            if( $fwd ) {
                // check the domain of this link is the right recordtype
                if( $link->domain_sid != $iterativeRecordType->sid ) {
                    throw new Exeception( "Domain of $linkname is not ".$iterativeRecordType->name );
                } 
                $iterativeRecordType = $link->range;
            } else {
                // backlink, so check range, set type to domain
                if( $link->range_sid != $iterativeRecordType->sid ) {
                    throw new Exeception( "Range of $linkname is not ".$iterativeRecordType->name );
                } 
                $iterativeRecordType = $link->domain;
            }
 
            $name = $iterativeRecordType->name;

            // in case we meet the same class twice, will fallback
            // to class, class2, class3, etc.
            $i=2;
            while( array_key_exists( $name, $context ) ) {
                $name = $link->name."$i";
                $i++;
            }
            $context[ $name ] = $iterativeRecordType;
           
        }

        return $context;
    }

    // actions 

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


    public function createRecordType( $name, $data ) 
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

    public function createLinkType( $name, $domain, $range, $data ) 
    {
        // these take exception if there's an issue
        LinkType::validateName( $name );
        LinkType::validateData( $data );

        // default minimum is zero. Default maximum is N (max null means unlimited)
        if( @$data["domain_min"]===null ) { $data["domain_min"]=0; }
        if( @$data["range_min"]===null ) { $data["range_min"]=0; }

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

    public function createRule( $data ) {
        Rule::validateData( $this, $data ); // rules need access to the schema to validate

        // all OK, let's make this rule
        $order = 0;
        $lastrule = $this->rules()->orderBy( 'order','desc' )->first();
        if( $lastrule ) { 
            $order = $lastrule->order + 1 ;
        }

        $record_type = new Rule();
        $record_type->documentRevision()->associate( $this );
        $record_type->order = $order;
        $record_type->data = json_encode( $data );

        $record_type->save();
        return $record_type;
    }

}


