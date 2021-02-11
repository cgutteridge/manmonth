<?php

namespace App\Console\Commands;

use App\Models\DocumentRevision;
use App\RecordReport;
use Illuminate\Console\Command;

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
     * @throws \App\Exceptions\ReportingException
     */
    public function handle()
    {
        /** @var DocumentRevision $docRev */
        /** @noinspection PhpUndefinedMethodInspection */
        $docRev = DocumentRevision::query()->orderBy('id', 'desc')->first();

        $loadingReportType = $docRev->reportTypeByName('loading');
        $report = $loadingReportType->makeReport();
        $report->save();
        /** @var RecordReport $recordReport */
        foreach ($report->recordReports() as $recordReport) {
            foreach ($recordReport->getColumns() as $key => $value) {
                print "$key: $value\n";
            }
            print "Loading target: " . $recordReport->getLoadingTarget() . "\n";
            print "Loading total : " . $recordReport->getLoadingTotal() . "\n";
            print "Loadings:\n";
            foreach ($recordReport->getLoadings() as $target => $loadings) {
                foreach ($loadings as $loading) {
                    print $loading['load'] . " (" . $loading['target'] . "." . @$loading['category'] . ") " . @$loading["description"] . "\n";
                }
            }
            // dump($recordReport);
            print "\n\n";
        }
        return;
    }
}
