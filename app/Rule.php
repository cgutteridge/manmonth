<?php

namespace App;

use Exception;
use Validator;


# TODO get a list of valid terms for an object (fields)
# TODO validate syntax
# TODO validate context
# TODO execute every last one of them

class Rule extends DocumentPart
{

    // there's probably a cleverer laravel way of doing this...
    static protected $actions = [
          MMAction\SetTarget::class,
          MMAction\AlterTarget::class,
          MMAction\ScaleTarget::class,
          MMAction\AssignLoad::class,
          MMAction\Debug::class,
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
        $actions = $this->actions();
        return $actions[$actionName];
    }    

    public static function validateData($docRev,$data) {

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
        $context = $docRev->getAbstractContext( $data["route"] );

        if( @$data["trigger"] ) {
            $trigger = new MMScript( $data["trigger"], $docRev, $context );
            $type = $trigger->type();
            if( $type != "#type" ) {
                // TODO better class of exception?
                throw new Exception( "Trigger must either be unset or evaluate to true/false. Currently evaluates to $type" );
            }
        }
      
        dd( "TODO: validated params" );  
        # TODO  validate params for action in context
    }
}






