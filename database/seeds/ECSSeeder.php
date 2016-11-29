<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;

class ECSSeeder extends Seeder
{
    /**
     * Run the database seeds for the draft ECS set up.
     *
     * @return void
     */
    public function run()
    {
        $doc = new App\Models\Document();
        $doc->name = "ECS Test Loadings, 2016-17";
        $doc->save();
        $doc->init(); // create default current revision
        $draft = $doc->createDraftRevision();

        // add schema

        $actorType = $draft->createRecordType("actor", [
            "label" => "Loadee",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true],
                ["name" => "pinumber", "label"=>"ID Number", "type"=>"string"],
                ["name" => "phdstudents", "label" => "PhD Students", "type" => "decimal", "min" => 0],
                ["name" => "istutor", "label" => "Is Tutor?", "type" => "boolean", "default" => false],
                ["name" => "firstyear", "label" => "First year of teaching?", "type" => "boolean", "default" => false],
                ["name" => "secondyear", "label" => "Second year of teaching?", "type" => "boolean", "default" => false],
            ]],
            "title_script" => "record.name"
        ]);


        $taskType = $draft->createRecordType("task", [
            "label" => "Task",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true],
                ["name" => "size", "label" => "Hours per unit", "type" => "decimal", "required" => true]
            ]],
            "title_script" => "record.name"
        ]);
        $atType = $draft->createRecordType("acttask", [
            "label" => "Task relationship",
            "data" => ["fields" => [
                ["name" => "ratio", "label" => "Ratio", "type" => "decimal", "default" => 1.0,],
                ["name" => "validuntil", "label"=>"Valid until academic year starting", "type"=>"decimal"],
                ["name" => "notes",  "label"=>"Notes", "type"=>"string" ]
            ]]
        ]);
        $draft->createLinkType('actor_to_acttask', $actorType, $atType,
            ["range_min" => 1, "range_max" => 1, "range_type" => "dependent",
                "label" => "task relationship", "inverse_label" => "actor"]);
        $draft->createLinkType('acttask_to_task', $atType, $taskType,
            ["domain_min" => 1, "domain_max" => 1, "domain_type" => "dependent",
                "label" => "task", "inverse_label" => "actor relationship"]);

        $modType = $draft->createRecordType("module", [
            "label" => "Module",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true],
                ["name" => "code", "label" => "Module Code", "type" => "string" ],
                ["name" => "semester", "label" => "Semester", "type" => "string" ],
                ["name" => "crn", "label" => "CRN", "type" => "string" ],
                ["name" => "students", "label" => "Class size", "type" => "decimal" ]
            ]],
            "title_script" => "record.code + ' ' + record.name + ' ' + record.semester"
        ]);

        // MODULE LEADER

        $modleadType = $draft->createRecordType("modlead", [
            "label" => "Module leader relationship",
            "data" => ["fields" => [
                ["name" => "ratio", "label" => "Ratio", "type" => "decimal", "default" => 1.0,],
                ["name" => "notes",  "label"=>"Notes", "type"=>"string" ]
            ]]
        ]);
        $draft->createLinkType('actor_leads', $actorType, $modleadType,
            ["range_min" => 1, "range_max" => 1, "range_type" => "dependent",
                "label" => "leads", "inverse_label" => "lead by"]);
        $draft->createLinkType('leads_module', $modleadType, $modType,
            ["domain_min" => 1, "domain_max" => 1, "domain_type" => "dependent",
                "label" => "module", "inverse_label" => "leader"]);

        // MODULE TEACHER

        $modteachType = $draft->createRecordType("modteach", [
            "label" => "Module teacher relationship",
            "data" => ["fields" => [
                ["name" => "ratio", "label" => "Ratio", "type" => "decimal", "default" => 1.0,],
                ["name" => "notes",  "label"=>"Notes", "type"=>"string" ]
            ]]
        ]);
        $draft->createLinkType('actor_teaches', $actorType, $modteachType,
            ["range_min" => 1, "range_max" => 1, "range_type" => "dependent",
                "label" => "teaches", "inverse_label" => "taught by"]);
        $draft->createLinkType('teaches_module', $modteachType, $modType,
            ["domain_min" => 1, "domain_max" => 1, "domain_type" => "dependent",
                "label" => "module", "inverse_label" => "teacher"]);

        // MODULE MODERATOR

        $modmodType = $draft->createRecordType("modmoderate", [
            "label" => "Module moderator relationship",
            "data" => ["fields" => [
                ["name" => "ratio", "label" => "Ratio", "type" => "decimal", "default" => 1.0,],
                ["name" => "notes",  "label"=>"Notes", "type"=>"string" ]
            ]]
        ]);
        $draft->createLinkType('actor_mods', $actorType, $modmodType,
            ["range_min" => 1, "range_max" => 1, "range_type" => "dependent",
                "label" => "moderates", "inverse_label" => "moderated by"]);
        $draft->createLinkType('mods_module', $modmodType, $modType,
            ["domain_min" => 1, "domain_max" => 1, "domain_type" => "dependent",
                "label" => "module", "inverse_label" => "moderator"]);




        // this can't be set until the links are created.
        $atType->title_script = "record<-actor_to_acttask.name+' <'+record.type+'> '+record->acttask_to_task.name";
        $atType->save();
        $modleadType->title_script = "record<-actor_leads.name+' leads '+record->leads_module.name";
        $modleadType->save();
        $modteachType->title_script = "record<-actor_teaches.name+' teaches '+record->teaches_module.name";
        $modteachType->save();
        $modmodType->title_script = "record<-actor_mods.name+' moderates '+record->mods_module.name";
        $modmodType->save();

        // Add records

        $alice = $actorType->createRecord(["name" => "Alice Aardvark",  "phdstudents" => 7]);
        $bobby = $actorType->createRecord(["name" => "Bobby Bananas", "phdstudents" => 2, "newbie" => true]);
        $clara = $actorType->createRecord(["name" => "Clara Crumb", "firsteyear" => true]);
        /*

        $small = $taskType->createRecord(["name" => "Small Job", "size" => 50]);
        $big = $taskType->createRecord(["name" => "Big Job", "size" => 100]);
        $misc = $taskType->createRecord(["name" => "Misc Job", "size" => 100]);

        $atType->createRecord(["type" => "leads"], ['acttask_to_task' => [$big]], ['actor_to_acttask' => [$alice]]);
        $atType->createRecord(["type" => "works"], ['acttask_to_task' => [$big]], ['actor_to_acttask' => [$alice]]);

        $atType->createRecord(["type" => "leads"], ['acttask_to_task' => [$small]], ['actor_to_acttask' => [$alice]]);
        $atType->createRecord(["type" => "works", "ratio" => 0.5], ['acttask_to_task' => [$small]], ['actor_to_acttask' => [$alice]]);
        $atType->createRecord(["type" => "works", "ratio" => 0.5], ['acttask_to_task' => [$small]], ['actor_to_acttask' => [$bobby]]);

        $atType->createRecord(["type" => "leads"], ['acttask_to_task' => [$misc]], ['actor_to_acttask' => [$clara]]);
        $atType->createRecord(["type" => "works", "ratio" => 0.8], ['acttask_to_task' => [$misc]], ['actor_to_acttask' => [$clara]]);
        $atType->createRecord(["type" => "works", "ratio" => 0.2], ['acttask_to_task' => [$misc]], ['actor_to_acttask' => [$bobby]]);
*/
        // add rules

        $loadingReportType = $draft->createReportType('loading', $actorType, ['title' => 'Loadings Report']);
        // people have a basic target load of 100
        $loadingReportType->createRule([
            "title" => "Default loading",
            "action" => "set_target",
            "params" => ["target" => "'loading'", "value" => 500]]);
        $loadingReportType->createRule([
            "title" => "40% for year one teachers",
            "trigger" => "actor.firstyear",
            "action" => "scale_target",
            "params" => ["target" => "'loading'", "factor" => 0.4]]);
        $loadingReportType->createRule([
            "title" => "70% for year two teachers",
            "trigger" => "actor.secondyear",
            "action" => "scale_target",
            "params" => ["target" => "'loading'", "factor" => 0.7]]);
        $loadingReportType->createRule([
            "title" => "Loading from working on task",
            "route" => ["actor_to_acttask"],
            "action" => "assign_load",
            "params" => [
                "description" => '\'Working on \'+acttask->acttask_to_task.name',
                "target" => "'loading'",
                "category" => "'teaching'",
                "load" => 'acttask->acttask_to_task.size * acttask.ratio'
            ]]);


        // people in the womats group get 3 units load per penguin
        $loadingReportType->createRule([
            "title" => "PhD supervision",
            "trigger" => "actor.phdstudents>0",
            "action" => "assign_load",
            "params" => [
                "description" => "actor.phdstudents+' PhD Supervision(s)'",
                "target" => "'loading'",
                "category" => "''",
                "load" => '30 * actor.phdstudents'
            ]]);

        // set column 'name', to actor.name
        $loadingReportType->createRule([
            "title" => "Set name column",
            "action" => "set_string_column",
            "params" => [
                "column" => "'name'",
                "value" => 'actor.name'
            ]]);

        $draft->publish();

        $draft2 = $doc->createDraftRevision();

        $draft2->scrap();

        $draft3 = $doc->createDraftRevision();


        // Now make some faked import data


        if (Schema::hasTable('imported_people')) {
            Schema::drop('imported_people');
        }
        Schema::create('imported_people', function (Blueprint $table) {
            $table->string('name');
            $table->string('email');
            $table->string('pinumber');
        });
        DB::table('imported_people')->insert(
            ['name' => "Miss Alpha", 'email' => 'alpha@example.com', 'pinumber' => "1000"]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Beta", 'email' => 'beta@example.com', 'pinumber' => "1001"]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Gamma", 'email' => 'gamma@example.com', 'pinumber' => "1002"]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Delta", 'email' => 'delta@example.com', 'pinumber' => "1003"]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Epsilon", 'email' => 'epsilon@example.com', 'pinumber' => "1004"]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Thingy", 'email' => 'thingy@example.com', 'pinumber' => "1005"]);


        if (Schema::hasTable('imported_courses_2016')) {
            Schema::drop('imported_courses_2016');
        }
        Schema::create('imported_courses_2016', function (Blueprint $table) {
            $table->string('name');
            $table->string('code');
            $table->string('crn');
            $table->integer('classsize');
        });
        DB::table('imported_courses_2016')->insert(
            ['name' => "Aardvark Studies", "code" => "AAAA1111", "crn" => "11111", "classsize" => 10]);
        DB::table('imported_courses_2016')->insert(
            ['name' => "Badger Studies", "code" => "BBBB2222", "crn" => "22222", "classsize" => 20]);
        DB::table('imported_courses_2016')->insert(
            ['name' => "Crocodile Studies", "code" => "CCCC3333", "crn" => "33333", "classsize" => 30]);
        DB::table('imported_courses_2016')->insert(
            ['name' => "Dragon Studies", "code" => "DDDD4444", "crn" => "44444", "classsize" => 40]);
        DB::table('imported_courses_2016')->insert(
            ['name' => "Elephant Studies", "code" => "EEEE5555", "crn" => "55555", "classsize" => 50]);
    }

}
