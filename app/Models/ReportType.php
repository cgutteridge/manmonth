<?php

namespace App\Models;

use App\Exceptions\MMValidationException;
use App\Exceptions\ReportingException;
use App\Http\TitleMaker;
use App\RecordReport;
use Exception;
use Validator;

/**
 * @property int base_record_type_sid
 * @property DocumentRevision documentRevision
 * @property string name
 * @property int sid
 * @property array data
 */
class ReportType extends DocumentPart
{
    /*************************************
     * RELATIONSHIPS
     *************************************/

    // none!

    /*************************************
     * READ FUNCTIONS
     *************************************/

    /**
     * @throws MMValidationException
     */
    public function validateName()
    {
        $validator = Validator::make(
            ['name' => $this->name],
            ['name' => 'required|alpha_dash|min:2|max:255']);

        if ($validator->fails()) {
            throw new MMValidationException("Validation fail in reportType.name: " . implode(", ", $validator->errors()->all()));
        }
    }

    /**
     * @throws MMValidationException
     */
    public function validateData()
    {
        $validator = Validator::make(
            $this->data,
            ['title' => 'required']
        );

        if ($validator->fails()) {
            throw new MMValidationException("Validation fail in reportType.data: " . implode(", ", $validator->errors()->all()));
        }
    }

    /**
     * @return array[Rule]
     */
    public function rules()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->rules";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->rules()
                ->where("report_type_sid", $this->sid)
                ->orderBy('rank')
                ->get();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Run this report type on the current document revision and produce a report object.
     * Doesn't save the object.
     * @return Report
     * @throws MMValidationException
     * @throws ReportingException
     */
    function makeReport()
    {
        $records = $this->baseRecordType()->records();
        $report = new Report();
        $report->documentRevision()->associate($this);
        $report->report_type_sid = $this->sid;

        foreach ($records as $record) {
            try {
                $recordReport = $this->recordReport($record);
            } catch (ReportingException $e) {
                $titleMaker = new TitleMaker();
                throw new ReportingException("In record " . $titleMaker->title($record) . ": " . $e->getMessage(), 0, $e);
            }
            $report->setRecordReport($record->sid, $recordReport);
        }

        return $report;
    }

    /**
     * note that this is NOT a laravel relation
     * @return RecordType
     */
    public function baseRecordType()
    {
        return $this->documentRevision->recordType($this->base_record_type_sid);
    }

    /*
     * for each rule get all possible contexts based on this record and the rule type 'route'
     * then apply the rule.
     * @param Record $record
     * @return RecordReport
     * @throws ReportingException
     */

    function recordReport($record)
    {
        $recordReport = new RecordReport();
        foreach ($this->rules() as $rule) {
            // apply this rule to every possible context based on the route
            /** @var Rule $rule */
            try {
                $rule->apply($record, $recordReport);
            } catch (Exception $e) {
                $titleMaker = new TitleMaker();
                throw new ReportingException("In rule " . $titleMaker->title($rule) . ": " . $e->getMessage(), 0, $e);
            }
        }
        return $recordReport;
    }

    /*************************************
     * ACTION FUNCTIONS
     *************************************/

    /**
     * @param array $data
     * @return Rule
     * @throws MMValidationException
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

        $rule->validate();
        $rule->save();

        return $rule;
    }

}


