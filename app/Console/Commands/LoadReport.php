<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DocumentRevision;
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
        $docRev = DocumentRevision::query()->orderBy( 'id','desc' )->first();

\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) { print $query->sql." - ".json_encode( $query->bindings )."\n"; });
        
        $loadingReport = $docRev->reportTypeByName( 'loading' );

        $report = $loadingReport->report();
        dd($report);
    }
}
