<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string status
 * @property Document document
 * @property User user
 * @property Collection reportTypes
 * @property Collection records
 * @property int id
 * @property RecordType[] recordTypes
 * @property Link[] links
 * @property LinkType[] linkTypes
 * @property Rule[] rules
 * @property boolean published
 * @property string user_username
 * @property string comment
 */
class DocumentRevision extends MMModel
{
    static $cache = [];

    /**
     * The relationship to the document this is a revision of.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document()
    {
        return $this->belongsTo('App\Models\Document');
    }

    /**
     * The user who created this revision.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo( User::class, "user_username", "username" );
    }

    /**
     * @param int $recordSid
     * @return Record
     */
    public function record($recordSid)
    {
        $relationCode = get_class($this) . "#" . $this->id . "->record/$recordSid";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->records()->where("sid", (int)$recordSid)->first();
        }
        return MMModel::$cache[$relationCode];
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
        $relationCode = get_class($this) . "#" . $this->id . "->reportType/$name";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->reportTypes()->where('name', $name)->first();
        }
        return MMModel::$cache[$relationCode];
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
     * @param int $sid
     * @return ReportType|null
     */
    public function reportType($sid)
    {
        $relationCode = get_class($this) . "#" . $this->id . "->reportType/sid/$sid";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->reportTypes()->where('sid', $sid)->first();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Return the configuration record
     * @return Record
     */
    public function configRecord()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->configRecord";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->configRecordType()->records()->first();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Return the configuration schema
     * @return RecordType
     */
    public function configRecordType()
    {
        return $this->recordTypeByName('config');
    }

    /**
     * @param string $name
     * @return RecordType|null
     */
    public function recordTypeByName($name)
    {
        $relationCode = get_class($this) . "#" . $this->id . "->recordType/name/$name";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->recordTypes()->where('name', $name)->first();
        }
        return MMModel::$cache[$relationCode];
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
     * @param int $sid
     * @return RecordType|null
     */
    public function recordType($sid)
    {
        $relationCode = get_class($this) . "#" . $this->id . "->recordType/sid/$sid";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->recordTypes()->where('sid', $sid)->first();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * @param string $name
     * @return LinkType
     */
    public function linkTypeByName($name)
    {
        $relationCode = get_class($this) . "#" . $this->id . "->linkType/$name";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->linkTypes()->where('name', $name)->first();
        }
        return MMModel::$cache[$relationCode];
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
        $relationCode = get_class($this) . "#" . $this->id . "->linkType/$linkTypeSid";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->linkTypes()->where("sid", (int)$linkTypeSid)->first();
        }
        return MMModel::$cache[$relationCode];
    }


    // actions 


    /**
     * @throws Exception
     */
    public function publish()
    {
        if ($this->status != "archive") {
            throw new Exception("Can't publish a revision that is not a commited to archive. status=" . $this->status);
        }
        if ($this->published) {
            throw new Exception("Revision is already published. Can't publish it again.");
        }

        $this->published = true;
        $this->save();
    }

    /**
     * @throws Exception
     */
    public function unpublish()
    {
        if ($this->status != "archive") {
            throw new Exception("Can't unpublish a revision that is not a commited to archive. status=" . $this->status);
        }
        if (!$this->published) {
            throw new Exception("Revision is already unpublished. Can't unpublish it again.");
        }

        $this->published = false;
        $this->save();
    }


    /**
     * @throws Exception
     */
    public function commit()
    {
        // can only commit if this is a draft
        if ($this->status != "draft") {
            throw new Exception("Can't commit a revision that is not a draft. status=" . $this->status);
        }

        $this->status = "archive";
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
     * @throws \App\Exceptions\MMValidationException
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
     * @throws \App\Exceptions\MMValidationException
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
     * @param string $name
     * @param RecordType $domain
     * @param RecordType $range
     * @param $properties
     * @return LinkType
     * @throws \App\Exceptions\MMValidationException
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
