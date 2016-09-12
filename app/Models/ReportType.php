<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use Illuminate\Support\Facades\Validator;
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rules() {
        return $this->documentRevision->rules()->getQuery()
            ->where( "report_type_sid", $this->sid )
            ->orderBy( 'rank')
            ->get();
    }

    // note that this is NOT a laravel relation
    /**
     * @return RecordType
     */
    public function baseRecordType()
    {
        return $this->documentRevision->recordTypes()->where( "sid", $this->base_record_type_sid )->first();
    }

    // candidate for a trait or something?
    var $dataCache;

    /**
     * @return array
     */
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateName() {

        $validator = Validator::make(
        [ 'name' => $this->name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new DataStructValidationException( "RecordType", "name", $this->name, $validator->errors() );
        }
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateData() {

        $validator = Validator::make(
          $this->data(),
          [ 'title' => 'required' ]
        );

        if($validator->fails()) {
            throw new DataStructValidationException( "ReportType", "data", $this->data(), $validator->errors() );
        }
    }

    /**
     * @param array $data
     * @return Rule
     */
    public function createRule($data ) {

        // all OK, let's make this rule
        $rank = 0;
        $lastrule = $this->rules()->sortByDesc( 'id' )->first();
        if( $lastrule ) { 
            $rank = $lastrule->rank + 1 ;
        }

        $rule = new Rule();
        $rule->documentRevision()->associate( $this->documentRevision );
        $rule->rank = $rank;
        $rule->report_type_sid = $this->sid;
        $rule->data = json_encode( $data );

        $rule->validateData();
        $rule->save();

        return $rule;
    }


    /**
     * Run this report type on the current document revision and produce a report object.
     * Doesn't save the object.
     * @param array $options
     * @return Report
     */
    function makeReport($options = []) {
        $records = $this->baseRecordType()->records;
        $report = $this->documentRevision->makeReport(); // will be an object when I know what shape it is!
        foreach( $records as $record ) {
            $report->setRecordReport( $record->sid, $this->recordReport( $record ) );
        }
        return $report;
    }


    /**
     * @param Record $record
     * @return RecordReport
     */
    function recordReport($record ) {
        // for each rule get all possible contexts based on this record and the rule type 'route'
        // then apply the rule
        $recordReport = new RecordReport();

        foreach( $this->rules() as $rule ) {
            // apply this rule to every possible context based on the route
            $rule->apply( $record, $recordReport );
        }
        return $recordReport;
    }

}


