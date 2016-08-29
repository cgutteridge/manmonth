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
        // add some record types  
        $actorType = $draft->newRecordType( "actor", array( 
          "fields"=>array( 
              array( "name"=>"name", "type"=>"string" ),
              array( "name"=>"group", "type"=>"string" ),
          )
        ));
        $taskType = $draft->newRecordType( "task", array( 
          "fields"=>array( 
              array( "name"=>"name", "type"=>"string" ),
              array( "name"=>"size", "type"=>"integer" ),
          )
        ));
        $atType = $draft->newRecordType( "actor_task", array(
          "fields"=>array( 
              array( "name"=>"type", "type"=>"string" ),
              array( "name"=>"ratio", "type"=>"decimal", "default"=>1.0 ),
          )
        ));

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

	$alice_leads_big = $atType->newRecord( array( "type"=>"leads" ) );
	$alice_leads_small = $atType->newRecord( array( "type"=>"leads" ) );
        $alice_on_big = $atType->newRecord( array( "type"=>"big" ) );
        $alice_on_small = $atType->newRecord( array( "type"=>"big", "ratio"=>0.5 ) );
        $bob_on_small = $atType->newRecord( array( "type"=>"big", "ratio"=>0.5 ) );

        $draft->publish();

        $draft2 = $doc->newDraftRevision();

        $draft2->scrap();

        $draft3 = $doc->newDraftRevision();

        // $this->call(UsersTableSeeder::class);
    }
}
