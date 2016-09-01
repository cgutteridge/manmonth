<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DocumentRevision;
use App\MMScript;

class LoadReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loadreport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a load report on the most recent document revision.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

new MMScript( "(100 + acttask->acttask_to_task.size * 3) * acttask.ratio" );


        $rev = DocumentRevision::query()->orderBy( 'id','desc' )->first();
        $report = $rev->report();
        dd($report);
        $this->comment("pip pip" );
    }
}
