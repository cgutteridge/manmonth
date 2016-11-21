<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;

// TODO add a field that knows that a revision is replacing a specific current revision. That can allow unscrapping if the scrap could still replace the current.

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
class DocumentRevision extends MMModel
{
    /**
     * The relationship to the document this is a revision of.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document()
    {
        return $this->belongsTo('App\Models\Document');
    }

    /**
     * @param int $recordSid
     * @return Record
     */
    public function record($recordSid)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->records()->where("sid", (int)$recordSid)->first();
    }

    /**
     * The relationship to the records in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records()
    {
        return $this->hasMany('App\Models\Record');
    }

    /**
     * The relationship to the links in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function links()
    {
        return $this->hasMany('App\Models\Link');
    }

    /**
     * Kinda the relationship but with order added.
     * Needs more thought.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rules()
    {
        return $this->hasMany('App\Models\Rule');
    }

    /**
     * @param string $name
     * @return ReportType
     */
    public function reportTypeByName($name)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->reportTypes()->where('name', $name)->first();
    }

    /**
     * The relationship to the report types in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportTypes()
    {
        return $this->hasMany('App\Models\ReportType');
    }

    /**
     * @param string $name
     * @return RecordType|null
     */
    public function recordTypeByName($name)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->recordTypes()->where('name', $name)->first();
    }

    /**
     * The relationship to the record types in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recordTypes()
    {
        return $this->hasMany('App\Models\RecordType');
    }

    /**
     * @param string $name
     * @return LinkType
     */
    public function linkTypeByName($name)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->linkTypes()->where('name', $name)->first();
    }

    /**
     * The relationship to the link types in this revision.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function linkTypes()
    {
        return $this->hasMany('App\Models\LinkType');
    }

    /**
     * @param $linkTypeSid
     * @return LinkType|null
     */
    public function linkType($linkTypeSid)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->linkTypes()->where("sid", (int)$linkTypeSid)->first();
    }


    // actions 

    /**
     * @throws Exception
     */
    public function publish()
    {
        // can only publish if this is a draft
        if ($this->status != "draft") {
            throw new Exception("Can't publish a revision that is not a draft. status=" . $this->status);
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
        if ($this->status != "draft") {
            throw new Exception("Can't scrap a revision that is not a draft. status=" . $this->status);
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
    public function createReportType($name, $baseRecordType, $data)
    {
        // these take exception if there's an issue

        $report_type = new ReportType();
        $report_type->documentRevision()->associate($this);
        $report_type->base_record_type_sid = $baseRecordType->sid;
        $report_type->name = $name;
        $report_type->data = $data;

        $report_type->validateName();
        $report_type->validateData();
        $report_type->save();

        return $report_type;
    }

    /**
     * @param $name
     * @param array $properties
     * @return RecordType
     */
    public function createRecordType($name, $properties)
    {
        $record_type = new RecordType();
        $record_type->documentRevision()->associate($this);
        $record_type->name = $name;
        $record_type->setProperties($properties);
        $record_type->validate();
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
        $report->documentRevision()->associate($this);
        return $report;
    }

    /**
     * @param string $name
     * @param RecordType $domain
     * @param RecordType $range
     * @param $properties
     * @return LinkType
     */
    public function createLinkType($name, $domain, $range, $properties)
    {
        $link_type = new LinkType();
        $link_type->documentRevision()->associate($this);
        $link_type->name = $name;
        $link_type->domain_sid = $domain->sid;
        $link_type->range_sid = $range->sid;
        $link_type->setProperties($properties);

        // these take exception if there's an issue
        $link_type->validate();
        $link_type->save();

        return $link_type;
    }

}
