<?php namespace App\Console\Commands;

use App\Exceptions\ReportingException;
use App\Models\Document;
use Illuminate\Console\Command;

class ExportReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export-report {document} {report_type} {--full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a report as CSV';

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
     * @throws \Exception
     */
    public function handle()
    {
        // nb. works on latest published revision, if any
        $documentid = $this->argument('document');
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

        $reportTypeName = $this->argument('report_type');
        $reportType = $revision->reportTypeByName($reportTypeName);
        if (!$reportType) {
            $this->error("No such report type available for the latest published revision of this document.");
            return 1;
        }

        $mode = "summary";
        if ($this->option("full")) {
            $mode = "full";
        }
        try {
            $rows = $reportType->buildTabularReportData($mode);
        } catch (ReportingException $e) {
            $this->error("Error generating report: " + $e->getMessage());
            return 1;
        }

        $FH = fopen('php://output', 'w');
        foreach ($rows as $row) {
            fputcsv($FH, $row);
        }
        fclose($FH);
        return 0;
    }
}

