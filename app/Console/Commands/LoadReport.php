<?php

namespace App\Console\Commands;

use App\RecordReport;
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
        /** @var DocumentRevision $docRev */
        /** @noinspection PhpUndefinedMethodInspection */
        $docRev = DocumentRevision::query()->orderBy( 'id','desc' )->first();

        $loadingReportType = $docRev->reportTypeByName( 'loading' );
        $report = $loadingReportType->makeReport();
        $report->save();
        /** @var RecordReport $recordReport */
        foreach($report->recordReports() as $recordReport) {
            foreach( $recordReport->getColumns() as $key=>$value ) {
                print "$key: $value\n";
            }
            foreach( $recordReport->getLoadingTargets() as $key=>$value ) {
                print "$key .. Target($value) .. Value(".$recordReport->getLoadingTotal($key).")\n";
            }
            print "Loadings:\n";
            foreach( $recordReport->getLoadings() as $loading ){
                print $loading['load']." (".$loading['target'].".".@$loading['category'].") ".@$loading["description"]."\n";
            }
            // dump($recordReport);
            print "\n\n";
        }
        return;
    }
}
