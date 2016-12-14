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
        $doc = new App\Models\Document();
        $doc->name = "Department of Studies Staff Loadings, 2015-16";
        $doc->save();
        $doc->init(); // create default current revision
        $draft = $doc->createDraftRevision();

        // add schema

        $actorType = $draft->createRecordType("actor", [
            "label" => "Actor",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true],
                ["name" => "group", "label" => "Group", "type" => "string"],
                ["name" => "penguins", "label" => "PhD Students", "type" => "decimal", "min" => 0],
                ["name" => "newbie", "label" => "New staff?", "type" => "boolean", "default" => false],
            ]],
            "title_script" => "record.name+' ('+record.group+')'"
        ]);
        $taskType = $draft->createRecordType("task", [
            "label" => "Task",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true],
                ["name" => "size", "label" => "Hours per unit", "type" => "decimal", "required" => true],
                ["name" => "new", "label" => "New task?", "type" => "boolean", "default" => false],
            ]],
            "title_script" => "record.name"
        ]);
        $movieType = $draft->createRecordType("movie", [
            "label" => "Movie",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true]
            ]],
            "title_script" => "record.name"
        ]);
        $atType = $draft->createRecordType("acttask", [
            "label" => "Actor/Task relationship",
            "data" => ["fields" => [
                ["name" => "type", "label" => "Relationship Type", "type" => "string", "required" => true],
                ["name" => "ratio", "label" => "Ratio", "type" => "decimal", "default" => 1.0,],
                ["name" => "notes",  "label"=>"Notes", "type"=>"string" ]
            ]]
        ]);
        $draft->createLinkType('actor_to_acttask', $actorType, $atType,
            ["range_min" => 1, "range_max" => 1, "range_type" => "dependent",
                "label" => "task relationship", "inverse_label" => "actor"]);
        $draft->createLinkType('acttask_to_task', $atType, $taskType,
            ["domain_min" => 1, "domain_max" => 1, "domain_type" => "dependent",
                "label" => "task", "inverse_label" => "actor relationship"]);
        $draft->createLinkType('watched', $actorType, $movieType,
            ["domain_min" => 1, "range_min" => 1, "domain_max" => 3,
                "label" => "watched", "inverse_label" => "watched by"]);

        // this can't be set until the links are created.
        $atType->title_script = "record<-actor_to_acttask.name+' <'+record.type+'> '+record->acttask_to_task.name";
        $atType->save();


        // Add records
        $movieType->createRecord(["name" => "Phantom"]);
        $movieType->createRecord(["name" => "Clones"]);
        $movieType->createRecord(["name" => "Revenge"]);
        $movieType->createRecord(["name" => "Hope"]);
        $movieType->createRecord(["name" => "Empire"]);
        $movieType->createRecord(["name" => "Jedi"]);

        $alice = $actorType->createRecord(["name" => "Alice Aardvark", "group" => "badgers", "penguins" => 7]);
        $bobby = $actorType->createRecord(["name" => "Bobby Bananas", "group" => "wombats", "penguins" => 2, "newbie" => true]);
        $clara = $actorType->createRecord(["name" => "Clara Crumb", "group" => "wombats", "penguins" => 0]);

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

        // add rules

        $loadingReportType = $draft->createReportType('loading', $actorType, ['title' => 'Loadings Report']);
        // people have a basic target load of 100 
        // people in the wombats group get 100 hours extra load target
        // people who are newbie have a 50% load target
        $loadingReportType->createRule([
            "title" => "Default loading",
            "action" => "set_target",
            "params" => ["target" => "'loading'", "value" => 500]]);
        $loadingReportType->createRule([
            "title" => "Wombat group +100 hours",
            "trigger" => "actor.group='wombat'",
            "action" => "alter_target",
            "params" => ["target" => "'loading'", "change" => 100]]);
        $loadingReportType->createRule([
            "title" => "Half loading for newbies",
            "trigger" => "actor.newbie",
            "action" => "scale_target",
            "params" => ["target" => "'loading'", "factor" => 0.5]]);
        // people not in group baders, on leading new modules get +20 hours
        $loadingReportType->createRule([
            "title" => "Loading for task leadership",
            "route" => ["actor_to_acttask", "acttask_to_task"],
            "trigger" => "acttask.type='leads'",
            "action" => "assign_load",
            "params" => [
                "target" => "'loading'",
                "category" => "'teaching'",
                "load" => "20*acttask.ratio",
                "description" => "'Loading for leading '+task.name"
            ]]);
        $loadingReportType->createRule([
            "title" => "Loading from working on task",
            "route" => ["actor_to_acttask"],
            "trigger" => "acttask.type='works'",
            "action" => "assign_load",
            "params" => [
                "description" => '\'Working on \'+acttask->acttask_to_task.name',
                "target" => "'loading'",
                "category" => "'teaching'",
                "load" => '(100 + acttask->acttask_to_task.size * 3) * acttask.ratio'
            ]]);

        // people in the badgers group get 10 units load per penguin
        $loadingReportType->createRule([
            "title" => "Penguin load for badgers",
            "trigger" => "actor.group='badgers'",
            "action" => "assign_load",
            "params" => [
                "description" => "'Penguin load for badgers'",
                "target" => "'loading'",
                "category" => "'penguin'",
                "load" => '10 * actor.penguins'
            ]]);

        // people in the womats group get 3 units load per penguin
        $loadingReportType->createRule([
            "title" => "Penguin load for wombats",
            "trigger" => "actor.group='wombats'",
            "action" => "assign_load",
            "params" => [
                "description" => "'Penguin load for wombats'",
                "target" => "'loading'",
                "category" => "'penguin'",
                "load" => '3 * actor.penguins'
            ]]);

        // set column 'name', to actor.name
        $loadingReportType->createRule([
            "title" => "Set name column",
            "action" => "set_string_column",
            "params" => [
                "column" => "'name'",
                "value" => 'actor.name'
            ]]);
        // set column 'group', to actor.group
        $loadingReportType->createRule([
            "title" => "Set name column",
            "action" => "set_string_column",
            "params" => [
                "column" => "'group'",
                "value" => 'actor.group'
            ]]);


        $draft->publish();

        $draft2 = $doc->createDraftRevision();

        $draft2->scrap();

        $draft3 = $doc->createDraftRevision();

#\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) { dump( $query->sql,$query->bindings ); });

        // inspect
        /*
        $actorType = $draft3->recordTypes()->where('name', 'actor')->first();
        foreach ($actorType->records as $actor) {
            print $actor->dumpText();
        }
        */
        // $this->call(UsersTableSeeder::class);
    }
}
