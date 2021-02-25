<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;

class ListReportTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-report-types {documentid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List available reports for a given document,';

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
        // nb. works on latest published revision, if any
        $documentid = $this->argument('documentid');
        $document = Document::where("id", $documentid)->first();
        if (!$document) {
            $this->error("No such document: '$documentid'");
            return 1;
        }
        $revision = $document->latestPublishedRevision();
        if (!$revision) {
            $this->error("Document '$documentid' exists but has no published revisions.");
            return 1;
        }

        print sprintf("Available reports for lastest published revision of document %d: \"%s\"\n", $documentid, $document->name);
        $reportTypes = $revision->reportTypes;
        foreach ($reportTypes as $reportType) {
            print sprintf("%5d %'.17s - \"%s\"\n", $reportType->id, " " . $reportType->name, $reportType->label);
        }
    }
}
