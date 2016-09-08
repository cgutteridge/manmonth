<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class DocumentRevision extends Model
{
    public function document() { return $this->belongsTo('App\Models\Document'); }

    public function recordTypes() { return $this->hasMany('App\Models\RecordType'); }

    public function records() { return $this->hasMany('App\Models\Record'); }

    public function linkTypes() { return $this->hasMany('App\Models\LinkType'); }

    public function links() { return $this->hasMany('App\Models\Link'); }

    // rules are generally ordered by rank 
    public function rules() { return $this->hasMany('App\Models\Rule')->orderBy( 'rank' ); }

    public function reportTypes() { return $this->hasMany('App\Models\ReportType'); }
  
    public function reportTypeByName( $name ) {
        return $this->reportTypes()->where( 'name', $name )->first();
    }

    public function recordTypeByName( $name ) {
        return $this->recordTypes()->where( 'name', $name )->first();
    }

    public function linkTypeByName( $name ) {
        return $this->linkTypes()->where( 'name', $name )->first();
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


    public function createReportType( $name, $baseRecordType, $data )
    {
        // these take exception if there's an issue
        ReportType::validateName( $name );
        // does this need to know the baseRecordType?
        ReportType::validateData( $data );

        // all OK, let's do this thing

        $report_type = new ReportType();
        $report_type->documentRevision()->associate( $this );
        $report_type->base_record_type_sid = $baseRecordType->sid;
        $report_type->name = $name;
        $report_type->data = json_encode( $data );

        $report_type->save();
        return $report_type;
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

}
