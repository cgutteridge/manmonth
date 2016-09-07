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
       
        $draft = $doc->createDraftRevision();

        // add schema

        $actorType = $draft->createRecordType( "actor", array( 
          "fields"=>array( 
              array( "name"=>"name", "type"=>"string", "required"=>true ),
              array( "name"=>"group", "type"=>"string" ),
              array( "name"=>"penguins", "type"=>"decimal" ),
              array( "name"=>"newbie", "type"=>"boolean", "default"=>false ),
          )
        ));
        $taskType = $draft->createRecordType( "task", array( 
          "fields"=>array( 
              array( "name"=>"name", "type"=>"string", "required"=>true ),
              array( "name"=>"size", "type"=>"integer", "required"=>true ),
              array( "name"=>"new", "type"=>"boolean", "default"=>false ),
          )
        ));
        $atType = $draft->createRecordType( "acttask", array(
          "fields"=>array( 
              array( "name"=>"type", "type"=>"string", "required"=>true ),
              array( "name"=>"ratio", "type"=>"decimal", "default"=>1.0, ),
          )
        ));
	$actorToActorTask = $draft->createLinkType( 'actor_to_acttask', $actorType, $atType, [ 
		'range_min'=>1, 
		'range_max'=>1, 
        ]);
	$actorTaskToTask = $draft->createLinkType( 'acttask_to_task', $atType, $taskType, [ 
		'domain_min'=>1, 
		'domain_max'=>1, 
        ]);

        // Add records

        $alice = $actorType->createRecord( [ "name"=>"Alice Aardvark", "group"=>"badgers", "penguins"=>7 ]);
        $bobby = $actorType->createRecord( [ "name"=>"Bobby Bananas", "group"=>"wombats", "penguins"=>2 ]);
        $clara = $actorType->createRecord( [ "name"=>"Clara Crumb", "group"=>"wombats", "penguins"=>0, "newbie"=>true ]);

        $small = $taskType->createRecord( [ "name"=>"Small Job", "size"=>50 ]);
        $big = $taskType->createRecord( [ "name"=>"Big Job", "size"=>100 ]);
        $misc = $taskType->createRecord( [ "name"=>"Misc Job", "size"=>100 ]);

	$atType->createRecord( [ "type"=>"leads" ], [ 'acttask_to_task'=>[$big] ],[ 'actor_to_acttask'=>[$alice] ] );
	$atType->createRecord( [ "type"=>"works" ], [ 'acttask_to_task'=>[$big] ],[ 'actor_to_acttask'=>[$alice] ] );

	$atType->createRecord( [ "type"=>"leads" ], [ 'acttask_to_task'=>[$small] ],[ 'actor_to_acttask'=>[$alice] ] );
	$atType->createRecord( [ "type"=>"works","ratio"=>0.5 ], [ 'acttask_to_task'=>[$small] ],[ 'actor_to_acttask'=>[$alice] ] );
	$atType->createRecord( [ "type"=>"works","ratio"=>0.5 ], [ 'acttask_to_task'=>[$small] ],[ 'actor_to_acttask'=>[$bobby] ] );

	$atType->createRecord( [ "type"=>"leads" ], [ 'acttask_to_task'=>[$misc] ],[ 'actor_to_acttask'=>[$clara] ] );
	$atType->createRecord( [ "type"=>"works","ratio"=>0.8 ], [ 'acttask_to_task'=>[$misc] ],[ 'actor_to_acttask'=>[$clara] ] );
	$atType->createRecord( [ "type"=>"works","ratio"=>0.2 ], [ 'acttask_to_task'=>[$misc] ],[ 'actor_to_acttask'=>[$bobby] ] );

        // add rules


        $loadingReportType = $draft->createReportType( $actorType,['title'=>'Loadings Report' ] );
        // people have a basic target load of 100 
        // people in the wombats group get 100 hours extra load target
        // people who are newbie have a 50% load target
        $loadingReportType->createRule( [ 
            "title"=>"Default loading",  
            "action"=>"set_target", 
            "params"=>[ "loading", 100 ]] );
        $loadingReportType->createRule( [ 
            "title"=>"Wombat group +100 hours",
            "trigger"=>"actor.group='wombat'", 
            "action"=>"alter_target", 
            "params"=>[ "loading", 100 ]] );
        $loadingReportType->createRule( [ 
            "title"=>"Half loading for newbies",
            "trigger"=>"actor.newbie", 
            "action"=>"scale_target", 
            "params"=>[ "loading", 0.5 ]] );
        // people not in group baders, on leading new modules get +20 hours  (TODO this should become a loading)
        $loadingReportType->createRule( [ 
            "title"=>"20 load for non-badger task leaders",
            "route"=>["actor_to_acttask","acttask_to_task"], 
            "trigger"=>"acttask.type='leads' & actor.group<>'badgers'", 
            "action"=>"assign_load", 
            "params"=>[ "loading", 20 ]] );
        $loadingReportType->createRule( [ 
            "title"=>"Loading from working on task",
            "route"=>["actor_to_acttask"],
            "trigger"=>"acttask.type='works'",
            "action"=>"assign_load", 
            "params"=>[ 
                "'Working on '+acttask->acttask_to_task.name",
                "loading", 
                "teaching",
                "(100 + acttask->acttask_to_task.size * 3) * acttask.ratio"
             ]]);

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

        $draft2 = $doc->createDraftRevision();

        $draft2->scrap();

        $draft3 = $doc->createDraftRevision();

#\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) { print $query->sql." - ".json_encode( $query->bindings )."\n"; });

        // inspect
        $actorType = $draft3->recordTypes()->where( 'name', 'actor' )->first();
        foreach( $actorType->records as $actor ) {
            print $actor->dumpText();
        }
        // $this->call(UsersTableSeeder::class);
    }
}
