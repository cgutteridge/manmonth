<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Role;
use Illuminate\Console\Command;

class ListRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-roles {documentid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List global roles, or roles for a document,';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $documentid = $this->argument('documentid');
        if ($documentid) {
            $document = Document::where("id", $documentid)->first();
            if (!$document) {
                $this->error("No such document: '$documentid'");
                return 1;
            }
            $roles = $document->roles;
            print sprintf("Roles for document %d: \"%s\"\n", $documentid, $document->name);
        } else {
            // global roles
            $roles = Role::whereNull('document_id')->get();
            print "Global roles\n";
        }

        foreach ($roles as $role) {
            $list = [];
            foreach ($role->permissions as $priv) {
                $list [] = $priv->name;
            }
            print sprintf("%5d %'.17s - \"%s\" (%s)\n", $role->id, " " . $role->name, $role->label, join(", ", $list));
        }

    }
}
