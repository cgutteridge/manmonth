<?php

namespace App\Models;

use Exception;
use App\Exceptions\DataStructValidationException;
use Validator;

class ReportType extends DocumentPart
{
    public function rules()
    {
        return $this->documentRevision->rules()->where( "report_type_sid", $this->sid );
    }

    // note that this is NOT a laravel relation
    public function baseRecordType()
    {
        return $this->documentRevision->recordTypes()->where( "sid", $this->base_record_type_sid )->first();
    }

    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    public function validateName() {

        $validator = Validator::make(
        [ 'name' => $this->name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new DataStructValidationException( "RecordType", "name", $this->name, $validator->errors() );
        }
    }
    public function validateData() {

        $validator = Validator::make(
          $this->data(),
          [ 'title' => 'required' ]
        );

        if($validator->fails()) {
            throw new DataStructValidationException( "ReportType", "data", $this->data(), $validator->errors() );
        }
    }

    public function createRule( $data ) {

        // all OK, let's make this rule
        $rank = 0;
        $lastrule = $this->rules()->orderBy( 'rank','desc' )->first();
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


    // run this report type on the current document revision and produce a report object
    function report($options = []) {
        $records = $this->baseRecordType()->records;
        $report = []; // will be an object when I know what shape it is!
        foreach( $records as $record ) {
            $report["records"][$record->sid] = $this->recordReport( $record );
        }
        dd(242);
    }

    function recordReport( $record ) {
        $rreport = [
            "data"=>$record->data,
            "targets"=>[],
            "loads"=>[],
            "columns"=>[],
        ];
        // for each rule get all possible contexts based on this record and the rule type 'route' 
        // then apply the rule 
        foreach( $this->rules as $rule ) {
            // apply this rule to every possible context based on the route
            $rule->apply( $record, $rreport );
        }
        return $rreport;
    }

}


