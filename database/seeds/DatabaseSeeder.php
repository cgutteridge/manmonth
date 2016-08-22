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
        $rt = $draft->newRecordType( "actor" );
        $r = $rt->newRecord();

        $draft->publish();

        $draft2 = $doc->newDraftRevision();

        $draft2->scrap();


        // $this->call(UsersTableSeeder::class);
    }
}
