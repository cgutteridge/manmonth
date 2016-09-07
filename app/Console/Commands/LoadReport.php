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
        $docRev = DocumentRevision::query()->orderBy( 'id','desc' )->first();

\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) { print $query->sql." - ".json_encode( $query->bindings )."\n"; });
        
$baseType = $docRev->recordTypeByName( 'actor' );
$context = $docRev->getAbstractContext( ['actor_to_acttask'] );
$exp = new MMScript( $cmd, $docRev, $context );
print "$cmd\n";
print $exp->textTree();

dd("todo");
        $report = $docRev->report();
        dd($report);
        $this->comment("pip pip" );
    }
}
