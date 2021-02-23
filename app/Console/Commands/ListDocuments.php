<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;

class ListDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all the documents and their IDs';

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
        $docs = Document::orderBy("id")->get();
        foreach ($docs as $doc) {
            print sprintf("%5d %'.60s - %s\n", $doc->id, " " . $doc->name, $doc->created_at);
        }
    }
}
