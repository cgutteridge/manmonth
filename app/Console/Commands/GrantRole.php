<?php

namespace App\Console\Commands;

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

        $user = User::where('username', $username)->first();
        if (!$user) {
            $this->error("No such user: '$username'");
            return 1;
        }

        if ($documentid) {
            $new_role = Role::where('document_id', $documentid)->where('name', $rolename)->first();
            if (!$new_role) {
                $this->error("No such role");
                return 1;
            }
        } else {
            // a global role
            $new_role = Role::whereNull('document_id')->where('name', $rolename)->first();
            if (!$new_role) {
                $this->error("No such role");
                return 1;
            }
        }

        // check user doesn't already have role
        foreach ($user->roles as $user_role) {
            if ($user_role->id == $new_role->id) {
                $this->warn("User already has that role");
                return;
            }
        }

        // OK the user doesn't already have the role, so add it
        $user->roles()->attach($new_role);
    }
}
