<?php

namespace App;

use Exception;
use Validator;


# TODO get a list of valid objects for a context
# TODO get a list of valid terms for an object (fields)
# TODO validate syntax
# TODO validate context
# TODO execute every last one of them

class Rule extends DocumentPart
{
    #    $draft->createRule( [ "action"=>"set-target", "params"=>[ "loading", 100 ]] );
    #    $draft->createRule( [ "trigger"=>"actor.group='wombat'", "action"=>"modify-target", "params"=>[ "loading", 100 ]] );
    #    $draft->createRule( [ "trigger"=>"actor.newbie", "action"=>"scale-target", "params"=>[ "loading", 0.5 ]] );
    #    $draft->createRule( [ "route"=>[ "actor_to_actor_task"=>"actor_task", "actor_task_to_task"=>"task" ] )
    #    $draft->createRule( [ "route"=>[ "mentor"=>"mentor" ] )// mentor is an actor

    protected $actions = [
    #     Commands\Inspire::class,
    #     Commands\DropTables::class,
    #     Commands\LoadReport::class,
    ];


    public static function validateData($docRev,$data) {

        $actions = array( "set-target", "modify-target", "scale-target", "loading" );

# TODO route
        $validator = Validator::make(
          $data,
          [ 'action' => 'required|string|in:'.join( ",", $actions ), 
            'trigger' => 'string',  
            'params' => 'array' ] );

        if($validator->fails()) {
            throw new ValidationException( "Rule", "data", $data, $validator->errors() );
        }


        // context is the types this is to operate on, not the specific instances
        // contains all the named record types
        // can throw exception is the contect is invalid, which we're cool with
        if( !@$data["route"] ) { $data["route"] = []; }
        $context = $docRev->getContext( $data["route"] );

        if( @$data["trigger"] ) {
            $trigger = new MMScript( $data["trigger"], $docRev, $context );
            // TODO check it's boolean
        }
        # TODO  validate params for action in context
        # TODO  validate trigger in context
    }
}






