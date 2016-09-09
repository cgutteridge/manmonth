<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DocumentRevision;

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

# \Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {$i=0;print preg_replace_callback( "/\?/", function($x) use ($query,&$i) { return $query->bindings[$i++]; }, $query->sql )."\n"; });
        
        $loadingReport = $docRev->reportTypeByName( 'loading' );

        $report = $loadingReport->report();
        dd($report);
        return;
    }
}
