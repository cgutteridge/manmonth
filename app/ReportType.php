<?php

namespace App;

use Exception;
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
            $link = $this->documentRevision->linkTypeByName( $linkName );
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

    public static function validateData($data) {

        $validator = Validator::make(
          $data,
          [ 'title' => 'required' ]
        );

        if($validator->fails()) {
            throw new ValidationException( "ReportType", "data", $data, $validator->errors() );
        }
    }

    public function createRule( $data ) {
        $this->validateRuleData( $data ); // rules need access to the schema to validate

        // all OK, let's make this rule
        $rank = 0;
        $lastrule = $this->rules()->orderBy( 'rank','desc' )->first();
        if( $lastrule ) { 
            $rank = $lastrule->rank + 1 ;
        }

        $record_type = new Rule();
        $record_type->documentRevision()->associate( $this );
        $record_type->rank = $rank;
        $record_type->data = json_encode( $data );

        $record_type->save();
        return $record_type;
    }


    public function validateRuleData($data) {

        $actions = Rule::actions();

        $validator = Validator::make(
          $data,
          [ 'action' => 'required|string|in:'.join( ",", array_keys($actions) ), 
            'trigger' => 'string',  
            'params' => 'array' ] );

        if($validator->fails()) {
            throw new ValidationException( "Rule", "data", $data, $validator->errors() );
        }

        // context is the types this is to operate on, not the specific instances
        // contains all the named record types
        // can throw exception is the contect is invalid, an we're happy to throw that exception
        if( !@$data["route"] ) { $data["route"] = []; }
        $context = $this->getAbstractContext( $data["route"] );

        if( @$data["trigger"] ) {
            $trigger = new MMScript( $data["trigger"], $this->documentRevision, $context );
            $type = $trigger->type();
            if( $type != "boolean" ) {
                // TODO better class of exception?
                throw new Exception( "Trigger must either be unset or evaluate to true/false. Currently evaluates to $type" );
            }
        }
     
        $action = Rule::action( $data["action"] );
        foreach( $action->fields as $field ) {
            if( !array_key_exists( $field->data["name"], $data["params"] ) ) {
                if( $field->required() ) {
                    throw new Exception( "Action ".$action->name." requires param '".$field->data["name"]."'" );
                }
                continue;
            }
            $script = new MMScript( $data["params"][ $field->data["name"] ], $this->documentRevision, $context );
            $type = $trigger->type();
            if( $type != $field->data["type"] ) {
                throw new Exception( "Action ".$action->name." param '".$field->data["name"]."' requires a value of type '".$field->data["type"]."' but got given '$type'" );
            }
        }
    }






}


