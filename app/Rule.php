<?php

namespace App;

use Exception;
use Validator;


# TODO execute every last one of them

class Rule extends DocumentPart
{
    public function reportType()
    {
        return $this->hasOne( 'App\RecordType', 'sid', 'report_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

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
        $actions = self::actions();
        return $actions[$actionName];
    }    
}
