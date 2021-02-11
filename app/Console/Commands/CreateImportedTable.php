<?php namespace App\Console\Commands;

use DB;
use Hash;
use Illuminate\Console\Command;
use App\Models\Document;

class CreateImportedTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-table'; 


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a table from a CSV file';

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
     */
    public function handle()
    {
        
    /*
    $this->comment(PHP_EOL . "MUNGE ALL THE THINGS (good luck)" . PHP_EOL);

        DB::beginTransaction();
	$doc = Document::find(1);
	$draft = $doc->draftRevision();
	$actorType = $draft->recordTypeByName( 'actor' );
        $count = 0;
	foreach( $actorType->records() as $record ) {
            $update = array();
            if( !empty($record->data["ug_project_loading"]) ) {
                 $update["ug_project_loading"] = $record->data["ug_project_loading"]*0.80;
            }
            if( !empty($record->data["msc_project_loading"]) ) {
                 $update["msc_project_loading"] = $record->data["msc_project_loading"]*0.60;
            }
            if( !empty($record->data["gdp_project_loading"]) ) {
                 $update["gdp_project_loading"] = $record->data["gdp_project_loading"]*0.32;
            }
	    if( count( $update ) ) {
	        print_r( $record->data );
	        print_r( $update );
	    	$record->updateData($update);
		$record->save();
	    }
#"ug_project_loading":"","msc_project_loading":"","gdp_project_loading":""
            ++$count;
	}

        DB::commit();

        $this->comment(PHP_EOL . "Did $count thing" . ($count == 1 ? "" : "s") . PHP_EOL);

        return;
*/
    }
    
}

