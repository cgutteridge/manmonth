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
		'domain_min'=>1, 
		'domain_max'=>1, 
		'range_min'=>1, 
		'range_max'=>1, 
        ]);
	$actorTaskToTask = $draft->newLinkType( 'actor_task_to_task', $atType, $taskType, [ 
		'domain_min'=>1, 
		'domain_max'=>1, 
		'range_min'=>1, 
		'range_max'=>1, 
        ]);

        // Add records

        $alice = $actorType->newRecord(
            array( "name"=>"Alice Aardvark", "group"=>"badgers" )
        );
        $bob = $actorType->newRecord(
            array( "name"=>"Bob Bananas", "group"=>"badgers" )
        );
        $small = $taskType->newRecord( 
            array( "name"=>"Small Job", "size"=>50 )
        );
        $big = $taskType->newRecord( 
            array( "name"=>"Big Job", "size"=>100 )
        );

	$aliceLeadsBig = $atType->newRecord( array( "type"=>"leads" ) );
	$aliceLeadsSmall = $atType->newRecord( array( "type"=>"leads" ) );
        $aliceOnBig = $atType->newRecord( array( "type"=>"big" ) );
        $aliceOnSmall = $atType->newRecord( array( "type"=>"big", "ratio"=>0.5 ) );
        $bobOnSmall = $atType->newRecord( array( "type"=>"big", "ratio"=>0.5 ) );

        // Add links
        
        $actorToActorTask->newLink( $alice, $aliceLeadsBig );
        $actorTaskToTask->newLink( $aliceLeadsBig, $big );
        $actorToActorTask->newLink( $alice, $aliceLeadsSmall );
        $actorTaskToTask->newLink( $aliceLeadsSmall, $small );
        $actorToActorTask->newLink( $alice, $aliceOnBig );
        $actorTaskToTask->newLink( $aliceOnBig, $big );
        $actorToActorTask->newLink( $alice, $aliceOnSmall, $small );
        $actorTaskToTask->newLink( $aliceOnSmall, $small );
        $actorToActorTask->newLink( $bob, $bobOnSmall, $small );
        $actorTaskToTask->newLink( $bobOnSmall, $small );

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
