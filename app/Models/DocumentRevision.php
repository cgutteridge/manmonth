<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Exception;

/**
 * @property string status
 * @property Document document
 * @property Collection reportTypes
 * @property Collection records
 * @property int id
 * @property RecordType[] recordTypes
 * @property Link[] links
 * @property LinkType[] linkTypes
 * @property Rule[] rules
 */
class DocumentRevision extends Model
{
    /**
     * The relationship to the document this is a revision of.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document() { return $this->belongsTo('App\Models\Document'); }

    /**
     * The relationship to the record types in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recordTypes() { return $this->hasMany('App\Models\RecordType'); }

    /**
     * The relationship to the records in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records() { return $this->hasMany('App\Models\Record'); }

    /**
     * The relationship to the link types in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function linkTypes() { return $this->hasMany('App\Models\LinkType'); }

    /**
     * The relationship to the links in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function links() { return $this->hasMany('App\Models\Link'); }

    /**
     * Kinda the relationship but with order added.
     * Needs more thought.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rules() { return $this->hasMany('App\Models\Rule');  }

    /**
     * The relationship to the report types in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportTypes() { return $this->hasMany('App\Models\ReportType'); }

    /**
     * @param string $name
     * @return ReportType
     */
    public function reportTypeByName($name ) {
        return $this->reportTypes()->where( 'name', $name )->first();
    }

    /**
     * @param string $name
     * @return RecordType
     */
    public function recordTypeByName($name ) {
        return $this->recordTypes()->where( 'name', $name )->first();
    }

    /**
     * @param string $name
     * @return LinkType
     */
    public function linkTypeByName($name ) {
        return $this->linkTypes()->where( 'name', $name )->first();
    }


    // actions 

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
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


    /**
     * @param string $name
     * @param RecordType $baseRecordType
     * @param array $data
     * @return ReportType
     */
    public function createReportType($name, $baseRecordType, $data )
    {
        // these take exception if there's an issue

        $report_type = new ReportType();
        $report_type->documentRevision()->associate( $this );
        $report_type->base_record_type_sid = $baseRecordType->sid;
        $report_type->name = $name;
        $report_type->data = $data;

        $report_type->validateName();
        $report_type->validateData();
        $report_type->save();

        return $report_type;
    }

    /**
     * @param string $name
     * @param array $data
     * @return RecordType
     */
    public function createRecordType($name, $data )
    {
        $record_type = new RecordType();
        $record_type->documentRevision()->associate( $this );
        $record_type->name = $name;
        $record_type->data = $data;

        $record_type->validateName();
        $record_type->validateData();
        $record_type->save();

        return $record_type;
    }

    /**
     * Make a report but don't save it to the database.
     *
     * @return Report
     */
    public function makeReport()
    {
        $report = new Report();
        $report->documentRevision()->associate( $this );
        return $report;
    }

    /**
     * @param string $name
     * @param RecordType $domain
     * @param RecordType $range
     * @param array $data
     * @return LinkType
     */
    public function createLinkType($name, $domain, $range, $data )
    {
        // default minimum is zero. Default maximum is N (max null means unlimited)
        if( @$data["domain_min"]===null ) { $data["domain_min"]=0; }
        if( @$data["range_min"]===null ) { $data["range_min"]=0; }

        // all OK, let's make this link type
        $link_type = new LinkType();
        $link_type->documentRevision()->associate( $this );
        $link_type->name = $name;
        $link_type->domain_sid = $domain->sid;
        $link_type->range_sid = $range->sid;
        $link_type->data = $data;

        // these take exception if there's an issue
        $link_type->validateName();
        $link_type->validateData();
        $link_type->save();

        return $link_type;
    }

}
