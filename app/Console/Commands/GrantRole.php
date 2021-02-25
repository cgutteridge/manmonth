<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class GrantRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant-role {username} {rolename} {documentid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant a role to a user';

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
        $username = $this->argument('username');
        $rolename = $this->argument('rolename');
        $documentid = $this->argument('documentid');

        $user = User::find($username);
        if (!$user) {
            $this->error("No such user: '$username'");
            return 1;
        }

        if ($documentid) {
            $document = Document::find($documentid);
            if (!$document) {
                $this->error("No such document: '$documentid'");
                return 1;
            }
            $new_role = Role::where('document_id', $documentid)->where('name', $rolename)->first();
            if (!$new_role) {
                $roleNames = [];
                $roles = $document->roles;
                foreach ($roles as $role) {
                    $roleNames [] = $role->name;
                }
                $this->error("No such role '$rolename' on document $documentid \"" . $document->name . "\".\n Valid options: " . join(", ", $roleNames) . ".");
                return 1;
            }
        } else {
            // a global role
            $new_role = Role::whereNull('document_id')->where('name', $rolename)->first();
            if (!$new_role) {
                $this->error("No such global role '$rolename'.");
                return 1;
            }
        }

        // check user doesn't already have role
        foreach ($user->roles as $user_role) {
            if ($user_role->id == $new_role->id) {
                $this->warn("User already has that role");
                return 0;
            }
        }

        // OK the user doesn't already have the role, so add it
        $user->roles()->attach($new_role);
        return 0;
    }
}
