<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $doc = new App\Document();
        $doc->name = "Department of Studies Staff Loadings, 2015-16";
        $doc->save();
        $doc->init(); // create default current revision
       
        $draft = $doc->newDraftRevision();

        // add schema

        $actorType = $draft->newRecordType( "actor", array( 
          "fields"=>array( 
              array( "name"=>"name", "type"=>"string", "required"=>true ),
              array( "name"=>"group", "type"=>"string" ),
              array( "name"=>"penguins", "type"=>"decimal" ),
              array( "name"=>"newbie", "type"=>"boolean" ),
          )
        ));
        $taskType = $draft->newRecordType( "task", array( 
          "fields"=>array( 
              array( "name"=>"name", "type"=>"string", "required"=>true ),
              array( "name"=>"size", "type"=>"integer", "required"=>true ),
          )
        ));
        $atType = $draft->newRecordType( "actor_task", array(
          "fields"=>array( 
              array( "name"=>"type", "type"=>"string", "required"=>true ),
              array( "name"=>"ratio", "type"=>"decimal", "default"=>1.0, ),
          )
        ));
	$actorToActorTask = $draft->newLinkType( 'actor_to_actor_task', $actorType, $atType, [ 
		'range_min'=>1, 
		'range_max'=>1, 
        ]);
	$actorTaskToTask = $draft->newLinkType( 'actor_task_to_task', $atType, $taskType, [ 
		'domain_min'=>1, 
		'domain_max'=>1, 
        ]);

        // Add records

        $alice = $actorType->newRecord( [ "name"=>"Alice Aardvark", "group"=>"badgers", "penguins"=>7 ]);
        $bobby = $actorType->newRecord( [ "name"=>"Bobby Bananas", "group"=>"wombats", "penguins"=>2 ]);
        $clara = $actorType->newRecord( [ "name"=>"Clara Crumb", "group"=>"wombats", "penguins"=>0, "newbie"=>true ]);

        $small = $taskType->newRecord( [ "name"=>"Small Job", "size"=>50 ]);
        $big = $taskType->newRecord( [ "name"=>"Big Job", "size"=>100 ]);
        $misc = $taskType->newRecord( [ "name"=>"Misc Job", "size"=>100 ]);

	$atType->newRecord( [ "type"=>"leads" ], [ 'actor_task_to_task'=>[$big] ],[ 'actor_to_actor_task'=>[$alice] ] );
	$atType->newRecord( [ "type"=>"works" ], [ 'actor_task_to_task'=>[$big] ],[ 'actor_to_actor_task'=>[$alice] ] );

	$atType->newRecord( [ "type"=>"leads" ], [ 'actor_task_to_task'=>[$small] ],[ 'actor_to_actor_task'=>[$alice] ] );
	$atType->newRecord( [ "type"=>"works","ratio"=>0.5 ], [ 'actor_task_to_task'=>[$small] ],[ 'actor_to_actor_task'=>[$alice] ] );
	$atType->newRecord( [ "type"=>"works","ratio"=>0.5 ], [ 'actor_task_to_task'=>[$small] ],[ 'actor_to_actor_task'=>[$bobby] ] );

	$atType->newRecord( [ "type"=>"leads" ], [ 'actor_task_to_task'=>[$misc] ],[ 'actor_to_actor_task'=>[$clara] ] );
	$atType->newRecord( [ "type"=>"works","ratio"=>0.8 ], [ 'actor_task_to_task'=>[$misc] ],[ 'actor_to_actor_task'=>[$clara] ] );
	$atType->newRecord( [ "type"=>"works","ratio"=>0.2 ], [ 'actor_task_to_task'=>[$misc] ],[ 'actor_to_actor_task'=>[$bobby] ] );

        // add rules
 
        // people have a basic target load of 100 
        // people in the wombats group get 100 hours extra load target
        // people who are newbie have a 50% load target
        $draft->newRule( [ "action"=>"set-target", "params"=>[ "loading", 100 ]] );
        $draft->newRule( [ "trigger"=>"actor.group='wombat'", "action"=>"modify-target", "params"=>[ "loading", 100 ]] );
        $draft->newRule( [ "trigger"=>"actor.newbie", "action"=>"scale-target", "params"=>[ "loading", 0.5 ]] );
        // people in the badgers group get 10 units load per penguin 
        // people in the womats group get 3 units load per penguin 

#path to entity (if any)
#trigger
#* boolean function
#action:
#* set target
#** target, value
#* add to target
#** target, value
#* multiply target
#** target, ratio
#* create loading
#** value
#** target
#** loading category
#** title
#params
#
        $draft->publish();

        $draft2 = $doc->newDraftRevision();

        $draft2->scrap();

        $draft3 = $doc->newDraftRevision();

#\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) { print $query->sql." - ".json_encode( $query->bindings )."\n"; });

        // inspect
        $actorType = $draft3->recordTypes()->where( 'name', 'actor' )->first();
        foreach( $actorType->records as $actor ) {
            print $actor->dumpText();
        }
        // $this->call(UsersTableSeeder::class);
    }
}
