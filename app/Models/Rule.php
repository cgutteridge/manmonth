<?php

namespace App\Models;

use Exception;
use Validator;


# TODO execute every last one of them

class Rule extends DocumentPart
{
    public function reportType()
    {
        return $this->hasOne( 'App\Models\ReportType', 'sid', 'report_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    // there's probably a cleverer laravel way of doing this...
    static protected $actions = [
          \App\MMAction\SetTarget::class,
          \App\MMAction\AlterTarget::class,
          \App\MMAction\ScaleTarget::class,
          \App\MMAction\AssignLoad::class,
          \App\MMAction\Debug::class,
    ];

    static protected $actionCache;
    public static function actions() {
        if( self::$actionCache ) { return self::$actionCache; }
        self::$actionCache = [];
        foreach( self::$actions as $class ) {
            $action = new $class();
            self::$actionCache[$action->name] = $action;
        }
        return self::$actionCache; 
    }
    public static function action( $actionName ) {
        $actions = self::actions();
        return $actions[$actionName];
    }    

    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    public function validateData() {

        $actions = Rule::actions();

        $validator = Validator::make(
          $this->data(),
          [ 'action' => 'required|string|in:'.join( ",", array_keys($actions) ), 
            'trigger' => 'string',  
            'params' => 'array' ] );

        if($validator->fails()) {
            throw new DataStructValidationException( "Rule", "data", $this->data(), $validator->errors() );
        }

        // context is the types this is to operate on, not the specific instances
        // contains all the named record types
        // can throw exception is the contect is invalid, an we're happy to throw that exception
        if( !@$this->data()["route"] ) { $this->data()["route"] = []; }
        $context = $this->getAbstractContext();

        if( @$this->data()["trigger"] ) {
            $trigger = new \App\MMScript( $this->data()["trigger"], $this->documentRevision, $context );
            $type = $trigger->type();
            if( $type != "boolean" ) {
                // TODO better class of exception?
                throw new Exception( "Trigger must either be unset or evaluate to true/false. Currently evaluates to $type" );
            }
        }
        $action = Rule::action( $this->data()["action"] );
        foreach( $action->fields as $field ) {
            if( !array_key_exists( $field->data["name"], $this->data()["params"] ) ) {
                if( $field->required() ) {
                    throw new Exception( "Action ".$action->name." requires param '".$field->data["name"]."'" );
                }
                continue;
            }
            $script = new \App\MMScript( $this->data()["params"][ $field->data["name"] ], $this->documentRevision, $context );
            $type = $script->type();
print $script->textTree();
        
            // not doing full autocasting but doing a special case to let decimal fields accpet integers
            $typeMatch = false; 
            if( $type == $field->data["type"] ) { $typeMatch = true; }
            if( $type == "integer" && $field->data["type"] == "decimal" ) { $typeMatch = true; }

            if( !$typeMatch ) {
                throw new Exception( "Action ".$action->name." param '".$field->data["name"]."' requires a value of type '".$field->data["type"]."' but got given '$type'" );
            }
        }
    }

    // get the absract context for this rule. Returns record & link types,
    // not specific records and links
    public function getAbstractContext() {
        $context = [];

        $baseRecordType = $this->reportType->baseRecordType();
        $context[$baseRecordType->name] = $baseRecordType;
        // add all the other objects in the route
        $iterativeRecordType = $baseRecordType;

        // simple case
        if( !isset($this->data()['route']) ) { return $context; }
        
        foreach( $this->data()['route'] as $linkName ) {
            $fwd = true;
            if( substr( $linkName, 0, 1 ) == "^" ) {
                $linkName = substr( $linkName, 1 );
                $fwd = false;
            }
            $link = $this->documentRevision->linkTypeByName( $linkName );
            if( !$link ) {
                // not sure what type of exception to make this (Script?)
                throw new Exeception( "Unknown linkName in context '$linkName'" );
            }
            
            if( $fwd ) {
                // check the domain of this link is the right recordtype
                if( $link->domain_sid != $iterativeRecordType->sid ) {
                    throw new Exeception( "Domain of $linkName is not ".$iterativeRecordType->name );
                } 
                $iterativeRecordType = $link->range;
            } else {
                // backlink, so check range, set type to domain
                if( $link->range_sid != $iterativeRecordType->sid ) {
                    throw new Exeception( "Range of $linkName is not ".$iterativeRecordType->name );
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

}
