<?php

use App\Models\Role;
use App\Models\User;
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


        /*
         * CREATE SCHEMA
         */

        $configType = $draft->configRecordType();
        $fields = $configType->data["fields"];
        $fields[] = [
            "name" => "exampaper",
            "label" => "Fixed hours for exam paper",
            "type" => "decimal",
            "required" => true,
        ];
        $fields[] = [
            "name" => "yearstarting",
            "label" => "Year of start of this allocation",
            "type" => "integer",
            "required" => true,
        ];
        $configType->setFields($fields);
        $configType->save();

        $actorType = $draft->createRecordType("actor", [
            "label" => "Loadee",
            "data" => [
                "external" => ["table" => "people", "key" => "pinumber", "local_key" => "pinumber"],
                "fields" => [
                    ["name" => "name", "label" => "Name", "type" => "string", "required" => true, "external" => "name", "mode" => "prefer_local"],
                    ["name" => "email", "label" => "Email", "type" => "string", "external" => "email", "mode" => "only_external"],
                    ["name" => "pinumber", "label" => "ID Number", "type" => "string"],
                    ["name" => "student_projects", "label" => "Students projects loading", "type" => "decimal", "min" => 0, "max" => 100],
                    ["name" => "tutorials", "label" => "Tutorials?", "type" => "boolean", "default" => false],
                    ["name" => "teaching_year", "label" => "Year of teaching", "type" => "option", "options" => "1|First\n2|Second\nother|Third or more", "default" => 'other']
                ]],
            "title_script" => "record.name"
        ]);


        $taskType = $draft->createRecordType("task", [
            "label" => "Task",
            "data" => ["fields" => [
                ["name" => "name", "label" => "Name", "type" => "string", "required" => true],
                ["name" => "size", "label" => "Hours per unit", "type" => "decimal", "required" => true],
                ["name" => "type", "label" => "Task type", "type" => "option", "options" => "teaching|Teaching\nadmin|Administration", "default" => 'admin']
            ]],
            "title_script" => "record.name"
        ]);
        $atType = $draft->createRecordType("acttask", [
            "label" => "Task relationship",
            "data" => ["fields" => [
                ["name" => "ratio", "label" => "Ratio", "type" => "decimal", "default" => 1.0,],
                ["name" => "validthrough", "label" => "Valid through year starting", "type" => "integer"],
                ["name" => "notes", "label" => "Notes", "type" => "string"]
            ]],
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
                ["name" => "code", "label" => "Module Code", "type" => "string"],
                ["name" => "semester", "label" => "Semester", "type" => "option", "options" => "s1|Semester 1\ns2|Semester 2\nboth|Semester 1 and 2\nother|Other or unknown"],
                ["name" => "crn", "label" => "CRN", "type" => "string"],
                ["name" => "students", "label" => "Class size", "type" => "integer"],
                ["name" => "lect", "label" => "Number of lectures", "type" => "integer"],
                ["name" => "cwk", "label" => "Coursework percentage", "type" => "decimal", "min" => 0, "max" => 100, "suffix" => "%"],
                ["name" => "labwk", "label" => "Labwork percentage", "type" => "decimal", "min" => 0, "max" => 100, "suffix" => "%"],
                ["name" => "exam", "label" => "Has exam", "type" => "boolean"]
            ]],
            "title_script" => "record.code + ' ' + record.name + ' ' + record.semester"
        ]);

        // MODULE TEACHER

        $modteachType = $draft->createRecordType("modteach", [
            "label" => "Module teacher relationship",
            "data" => ["fields" => [
                ["name" => "percent", "label" => "Teaching Percentage", "type" => "decimal", "default" => 100, "suffix" => "%"],
                ["name" => "leader", "label" => "Is leader?", "type" => "boolean", "default" => false],
                ["name" => "new", "label" => "New to teaching this?", "type" => "boolean", "default" => false],
                ["name" => "notes", "label" => "Notes", "type" => "string"]
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
                ["name" => "notes", "label" => "Notes", "type" => "string"]
            ]]
        ]);
        $draft->createLinkType('actor_mods', $actorType, $modmodType,
            ["range_min" => 1, "range_max" => 1, "range_type" => "dependent",
                "label" => "moderates", "inverse_label" => "moderated by"]);
        $draft->createLinkType('mods_module', $modmodType, $modType,
            ["domain_min" => 1, "domain_max" => 1, "domain_type" => "dependent",
                "label" => "module", "inverse_label" => "moderator"]);


        // this can't be set until the links are created.
        $atType->title_script = "if( isset(record.validthrough) | record.validthrough < config.yearstarting , '**EXPIRED '+record.validthrough+'** ','' )+record<-actor_to_acttask.name+' <'+record.ratio+'> '+record->acttask_to_task.name";
        $atType->save();

        $modteachType->title_script = "record<-actor_teaches.name+' teaches '+record->teaches_module.name";
        $modteachType->save();
        $modmodType->title_script = "record<-actor_mods.name+' moderates '+record->mods_module.name";
        $modmodType->save();


        /*
         * DEFAULT CONFIGURATION
         */

        $config = $draft->configRecord();
        $config->updateData([
            'exampaper' => 7,
            'yearstarting' => 2017
        ]);
        $config->save();


        /*
         * RECORDS AND LINKS
         */

        $alice = $actorType->createRecord(["name" => "Alice Aardvark", "student_projects" => 100]);
        $bobby = $actorType->createRecord(["name" => "Bobby Bananas", "student_projects" => 100, "tutorials" => true]);
        $clara = $actorType->createRecord(["name" => "Clara Crumb", "teaching_year" => '1']);

        $comp1234 = $modType->createRecord(["name" => "Fish studies", "code" => "comp1234", "semester" => "s1",
            "students" => 101, "lect" => 10, "cwk" => 30, "labwk" => 0, "exam" => true
        ]);
        $comp1235 = $modType->createRecord(["name" => "Giraffe studies", "code" => "comp1235", "semester" => "s1",
            "students" => 11, "lect" => 10, "cwk" => 50, "labwk" => 50, "exam" => false
        ]);
        $comp1236 = $modType->createRecord(["name" => "Hippo studies", "code" => "comp1236", "semester" => "s2",
            "students" => 21, "lect" => 20, "cwk" => 0, "labwk" => 10, "exam" => true
        ]);

        $small = $taskType->createRecord(["name" => "Small Job", "size" => 50]);
        $big = $taskType->createRecord(["name" => "Big Job", "size" => 100]);
        $misc = $taskType->createRecord(["name" => "Misc Job", "size" => 100]);

        $atType->createRecord([], ['acttask_to_task' => [$big]], ['actor_to_acttask' => [$alice]]);
        $atType->createRecord(["validthrough" => 2012], ['acttask_to_task' => [$big]], ['actor_to_acttask' => [$bobby]]);
        $atType->createRecord(["validthrough" => 2017], ['acttask_to_task' => [$big]], ['actor_to_acttask' => [$clara]]);

        $atType->createRecord(["units" => 1], ['acttask_to_task' => [$small]], ['actor_to_acttask' => [$alice]]);
        $atType->createRecord(["units" => 2], ['acttask_to_task' => [$small]], ['actor_to_acttask' => [$bobby]]);

        $atType->createRecord(["units" => 0.8], ['acttask_to_task' => [$misc]], ['actor_to_acttask' => [$clara]]);
        $atType->createRecord(["units" => 0.2], ['acttask_to_task' => [$misc]], ['actor_to_acttask' => [$bobby]]);

        $modteachType->createRecord(["percent" => 100, "new" => false, "leader" => true], ['teaches_module' => [$comp1234]], ['actor_teaches' => [$alice]]);
        $modmodType->createRecord(["notes" => "Example notes..."], ['mods_module' => [$comp1234]], ['actor_mods' => [$bobby]]);


        /*
         * REPORTS AND RULES
         */

        $loadingReportType = $draft->createReportType('loading', $actorType, ['title' => 'Loadings Report']);
        // people have a basic target load of 100
        $loadingReportType->createRule([
            "title" => "Default loading",
            "action" => "set_target",
            "params" => ["value" => 500]]);
        $loadingReportType->createRule([
            "title" => "40% for year one teachers",
            "trigger" => "actor.teaching_year='1'",
            "action" => "scale_target",
            "params" => ["factor" => 0.4]]);
        $loadingReportType->createRule([
            "title" => "70% for year two teachers",
            "trigger" => "actor.teaching_year='2'",
            "action" => "scale_target",
            "params" => ["factor" => 0.7]]);
        $loadingReportType->createRule([
            "title" => "Teaching category",
            "action" => "add_category",
            "params" => [
                "category" => "'teaching'",
                "background_color" => "'lightblue'",
                "label" => "'Teaching'"
            ]
        ]);
        $loadingReportType->createRule([
            "title" => "Admin category",
            "action" => "add_category",
            "params" => [
                "category" => "'admin'",
                "background_color" => "'lightgrey'",
                "label" => "'Admin'"
            ]
        ]);
        $loadingReportType->createRule([
            "title" => "Loading from working on task",
            "route" => ["actor_to_acttask", "acttask_to_task"],
            "trigger" => "!isset(acttask.validthrough) | acttask.validthrough>=config.yearstarting",
            "action" => "assign_load",
            "params" => [
                "description" => 'task.name',
                "category" => "string(task.type)",
                "load" => 'task.size * acttask.ratio'
            ]]);
        $loadingReportType->createRule([
            "title" => "Loading from teaching a module",
            "route" => ["actor_teaches", "teaches_module"],
            "action" => "assign_load",
            "params" => [
                "description" => 'modteach->teaches_module.code+\' (Teaching)\'',
                "category" => "'teaching'",
                "link" => "modteach",
                "load" => 'floor((modteach.percent/100)*(module.lect*2+module.students*(module.cwk/100*2+module.labwk/100))+if( module.exam, module.students+config.exampaper, 0.0))'
            ]]);

        //basic unit load = LECT*2 + STUD*(CWK*2 + LABWK)
        // plus exam:
        //EXAM = 0 (if no exam) EXAM = STUD + EP (if there is an exam)

        $loadingReportType->createRule([
            "title" => "Loading from moderating a module",
            "route" => ["actor_mods", "mods_module"],
            "action" => "assign_load",
            "params" => [
                "description" => 'module.code+\' (Teaching)\'',
                "category" => "'teaching'",
                "link" => "modmoderate",
                "load" => '10'
            ]]);
        $loadingReportType->createRule([
            "title" => "Student projects",
            "trigger" => "actor.student_projects>0",
            "action" => "assign_load",
            "params" => [
                "description" => "'Projects (student) '+actor.student_projects+'% '",
                "category" => "'teaching'",
                "link" => "actor",
                "load" => '80 * (actor.student_projects/100)'
            ]]);
        $loadingReportType->createRule([
            "title" => "Tutorials",
            "trigger" => "actor.tutorials",
            "action" => "assign_load",
            "params" => [
                "description" => "'Tutorials'",
                "link" => "actor",
                "category" => "'teaching'",
                "load" => '25'
            ]]);

        // set column 'name', to actor.name
        $loadingReportType->createRule([
            "title" => "Set name column",
            "action" => "set_string_column",
            "params" => [
                "column" => "'name'",
                "value" => 'actor.name'
            ]]);


        $moduleReportType = $draft->createReportType('teaching', $modType, ['title' => 'Teaching Allocation Report']);

        // set column 'name', to module.name
        $moduleReportType->createRule([
            "title" => "Set name column",
            "action" => "set_string_column",
            "params" => ["column" => "'name'", "value" => 'module.name']]);
        $moduleReportType->createRule([
            "title" => "Set code column",
            "action" => "set_string_column",
            "params" => ["column" => "'code'", "value" => 'module.code']]);
        $moduleReportType->createRule([
            "title" => "Set semester column",
            "action" => "set_string_column",
            "params" => ["column" => "'semester'", "value" => 'string(module.semester)']]);

        $moduleReportType->createRule([
            "title" => "Target teaching",
            "action" => "set_target",
            "params" => ["value" => 100, "units" => "'percent'"]]);
        $moduleReportType->createRule([
            "title" => "Teachers",
            "route" => ["^teaches_module", "^actor_teaches"],
            "action" => "assign_load",
            "link" => "actor",
            "params" => [
                "description" => "actor.name",
                "load" => 'modteach.percent'
            ]]);


        $draft->publish();

        $draft2 = $doc->createDraftRevision();

        $draft2->scrap();

        $draft3 = $doc->createDraftRevision();


        /*
         * ROLES and PERMISSIONS
         */


        $adminRole = new Role();
        $adminRole->name = "admin";
        $adminRole->label = "Document Administrator";
        $adminRole->document()->associate($doc);
        $adminRole->save();
        $adminRole->assign("view-current");
        $adminRole->assign("view-archive");
        $adminRole->assign("view-draft");
        $adminRole->assign("view-scrap");
        $adminRole->assign("edit-data");
        $adminRole->assign("edit-schema");
        $adminRole->assign("edit-reports");

        $staffRole = new Role();
        $staffRole->name = "staff";
        $staffRole->label = "Staff";
        $staffRole->document()->associate($doc);
        $staffRole->save();
        $staffRole->assign("view-current");


        /*
         * TEST USERS
         */

        $password = $this->randomPassword();

        // Erase all existing users. Could cause weirdness!
        DB::table('users')->delete();

        // an admin
        $dave = new User();
        $dave->name = "Dave Doberman";
        $dave->email = "dave@example.org";
        $dave->password = Hash::make($password);
        $dave->save();
        $dave->assign($adminRole);
        $dave->assign($staffRole);

        // a member of staff
        $alice = new User();
        $alice->name = "Alice Aardvark";
        $alice->email = "alice@example.org";
        $alice->password = Hash::make($password);

        $alice->save();
        $alice->assign($staffRole);

        // someone with no right to see anything.
        $edward = new User();
        $edward->name = "Edward Eagle";
        $edward->email = "edward@example.org";
        $edward->password = Hash::make($password);

        $edward->save();

        print "Created users with password '$password'.\n";


        /*
         * FAKE IMPORTED DATA
         */

        if (Schema::hasTable('imported_people')) {
            Schema::drop('imported_people');
        }
        Schema::create('imported_people', function (Blueprint $table) {
            $table->string('name');
            $table->string('email');
            $table->string('pinumber');
            $table->integer('phdstudents')->nullable();
        });
        DB::table('imported_people')->insert(
            ['name' => "Miss Alpha", 'email' => 'alpha@example.com', 'pinumber' => "1000", "phdstudents" => 5]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Beta", 'email' => 'beta@example.com', 'pinumber' => "1001", "phdstudents" => 5]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Gamma", 'email' => 'gamma@example.com', 'pinumber' => "1002", "phdstudents" => 5]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Delta", 'email' => 'delta@example.com', 'pinumber' => "1003", "phdstudents" => 5]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Epsilon", 'email' => 'epsilon@example.com', 'pinumber' => "1004", "phdstudents" => 5]);
        DB::table('imported_people')->insert(
            ['name' => "Miss Thingy", 'email' => 'thingy@example.com', 'pinumber' => "1005", "phdstudents" => null]);


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

    function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

}
