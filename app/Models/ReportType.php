<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use Validator;
use App\RecordReport;

/**
 * @property int base_record_type_sid
 * @property DocumentRevision documentRevision
 * @property string name
 * @property int sid
 * @property array data
 */
class ReportType extends DocumentPart
{
    /**
     * @return array[Rule]
     */
    public function rules()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->rules()
            ->where("report_type_sid", $this->sid)
            ->orderBy('rank')
            ->get();
    }

    /**
     * note that this is NOT a laravel relation
     * @return RecordType
     */
    public function baseRecordType()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->recordTypes()
            ->where("sid", $this->base_record_type_sid)
            ->first();
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateName()
    {

        $validator = Validator::make(
            ['name' => $this->name],
            ['name' => 'required|alpha_dash|min:2|max:255']);

        if ($validator->fails()) {
            throw new DataStructValidationException("Validation fail in reportType.name: " . join(", ", $validator->errors()));
        }
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateData()
    {

        $validator = Validator::make(
            $this->data,
            ['title' => 'required']
        );

        if ($validator->fails()) {
            throw new DataStructValidationException("Validation fail in reportType.data: " . join(", ", $validator->errors()));
        }
    }

    /**
     * @param array $data
     * @return Rule
     */
    public function createRule($data)
    {

        // all OK, let's make this rule
        $rank = 0;
        /** @noinspection PhpUndefinedMethodInspection */
        $lastrule = $this->rules()->sortByDesc('id')->first();
        if ($lastrule) {
            $rank = $lastrule->rank + 1;
        }

        $rule = new Rule();
        $rule->documentRevision()->associate($this->documentRevision);
        $rule->rank = $rank;
        $rule->report_type_sid = $this->sid;
        $rule->data = $data;

        $rule->validateData();
        $rule->save();

        return $rule;
    }


    /**
     * Run this report type on the current document revision and produce a report object.
     * Doesn't save the object.
     * @return Report
     */
    function makeReport()
    {
        $records = $this->baseRecordType()->records;
        $report = $this->documentRevision->makeReport(); // will be an object when I know what shape it is!
        foreach ($records as $record) {
            $report->setRecordReport($record->sid, $this->recordReport($record));
        }
        return $report;
    }


    /*
     * for each rule get all possible contexts based on this record and the rule type 'route'
     * then apply the rule.
     * @param Record $record
     * @return RecordReport
     */
    function recordReport($record)
    {

        $recordReport = new RecordReport();

        foreach ($this->rules() as $rule) {
            // apply this rule to every possible context based on the route
            $rule->apply($record, $recordReport);
        }
        return $recordReport;
    }

}


