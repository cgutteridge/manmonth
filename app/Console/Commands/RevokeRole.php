<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class RevokeRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revoke-role {username} {rolename} {documentid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke a role from a user';

    /**
     * Create a doomed_command instance.
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
            $doomed_role = Role::where('document_id', $documentid)->where('name', $rolename)->first();
            if (!$doomed_role) {
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
            $doomed_role = Role::whereNull('document_id')->where('name', $rolename)->first();
            if (!$doomed_role) {
                $this->error("No such role");
                return 1;
            }
        }

        // check user doesn't already have role
        $has_role = false;
        foreach ($user->roles as $user_role) {
            if ($user_role->id == $doomed_role->id) {
                $has_role = true;
            }
        }
        if (!$has_role) {
            $this->warn("User does not have that role");
            return;
        }
        // OK the user doesn't already have the role, so add it
        $user->roles()->detach($doomed_role);
    }
}
