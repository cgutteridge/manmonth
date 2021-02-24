<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Schema;

class ImportTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-table {tablename} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a file into an imported_ tablename in the database';

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
        $tablename = "imported_" . $this->argument('tablename');
        $filename = $this->argument('filename');
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $tablename)) {
            $this->error("tablename names can only contain alphanumerics and underscores");
            return 1;
        }
        // check the tablename does not exist
        if (Schema::hastable($tablename)) {
            $this->error("tablename $tablename already exists");
            return 1;
        }
        // check the file does exist
        if (!file_exists($filename)) {
            $this->error("File $filename does not exist.");
            return 1;
        }
        // read the file and check contents
        if (($handle = fopen($filename, "r")) === FALSE) {
            $this->error("Could not read $filename.");
            return 1;
        }
        // read the CSV file
        $headings = fgetcsv($handle, 10000, ",");
        $ok = true;

        /** @var string $heading */
        foreach ($headings as $heading) {
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $heading)) {
                $this->error("Headings can only contain alphanumerics and underscores: \"$heading\" does not conform to this.");
                $ok = false;
            }
        }
        $records = [];
        $row_n = 1;
        while ($ok && (($row = fgetcsv($handle, 10000, ",")) !== FALSE)) {
            $row_n++;
            if (count($row) != count($headings)) {
                $this->error("Row length mismatch. Row $row_n has " . count($row) . " but there are " . count($headings) . " headings.");
                $ok = false;
            }
            $record = [];
            for ($c = 0; $c < count($row); $c++) {
                $record[$headings[$c]] = $row[$c];
                if( strlen($row[$c])>255 ) {
                    $this->warn( sprintf( "Row %d column %s is length %d and will be truncated to 255.", $row_n, $headings[$c], strlen($row[$c])));
                }
            }
            $records [] = $record;
        }
        fclose($handle);
        if (!$ok) {
            return 1;
        }
        // import the tablename
        DB::beginTransaction();
        Schema::create($tablename, function (Blueprint $tablename) use ($headings) {
            foreach( $headings as $heading ) {
                $tablename->string($heading);
                $tablename->index([$heading]);
            }
        });
        $table = DB::table( $tablename );
        foreach( $records as $record ) {
            $table->insert( $record );
        }
        DB::commit();
        $this->comment( sprintf( "Imported %d record%s\n" ,count($records), count($records) == 1 ? "" : "s") );
    }
}
