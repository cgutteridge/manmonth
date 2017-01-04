<?php namespace App\Console\Commands;

use DB;
use Hash;
use Illuminate\Console\Command;

class SetAllPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setallpasswords 
                            {password? : (optional) The password to set. If not set you will be prompted for it.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set all users to the same password';

    /**
     * Create a new command instance.
     *
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
        $this->comment(PHP_EOL . "This tool is intended for test systems only. It changes every user's password to the same thing." . PHP_EOL);

        $password = $this->argument('password');
        if (!isset($password)) {
            $password = $this->secret('What password do you want all users to have?');
            $password2 = $this->secret('Please confirm password');

            if ($password != $password2) {
                exit("Aborting, passwords did not match.\n");
            }
        }

        $count = 0;
        DB::beginTransaction();
        foreach (DB::table("users")->get() as $user) {
            DB::table("users")
                ->where("id", $user->id)
                ->update(array("password" => Hash::make($password)));
            ++$count;
        }
        DB::commit();

        $this->comment(PHP_EOL . "Reset $count password" . ($count == 1 ? "" : "s") . PHP_EOL);

        return;
    }
    
}

