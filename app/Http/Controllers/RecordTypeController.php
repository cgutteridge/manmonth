<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\RecordType;
use DB;
use Exception;
use Illuminate\Http\RedirectResponse;
use Redirect;
use Response;

class RecordTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param RecordType $recordType
     * @return Response
     */
    public function show(RecordType $recordType)
    {
        $this->authorize('view', $recordType);

        return view('recordType.show', [
            "recordType" => $recordType,
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)]);
    }


    /**
     * Display the records of this type
     *
     * @param RecordController $recordController
     * @param RecordType $recordType
     * @return Response
     */
    public function records(RecordController $recordController, RecordType $recordType)
    {
        $this->authorize('view', $recordType);

        $recordBlocks = [];
        foreach ($recordType->records as $record) {
            $recordBlocks[] = [
                "data" => $recordController->recordDataBlock($record),
                "links" => [],
                "returnURL" => $this->linkMaker->url($recordType, "records"),
                "record" => $record,
                "swimLanes" => false
            ];
        }
        return view('recordType.records', [
            "recordType" => $recordType,
            "records" => $recordBlocks,
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)]);
    }


    /**
     * Display the records of this type
     *
     * @param RecordType $recordType
     * @return Response
     */
    public function externalRecords(RecordType $recordType)
    {
        $MAX_SIZE = 200;
        $this->authorize('view', $recordType);

        $issues = [];
        $external_fields = $recordType->externalColumns();
        if (count($external_fields) == 0) {
            $issues [] = "External data not configured for this type of record.";
        }
        if (count($issues)) {
            return Redirect::to($this->linkMaker->url($recordType))
                ->withErrors($issues);
        }

        $filters = $this->requestProcessor->filters();

        $ext = $recordType->data['external'];

        $tableName = 'imported_' . $ext["table"];
        $table = DB::table($tableName)->distinct();
        $filteredTable = DB::table($tableName)->distinct();

        $size = $table->count();
        foreach ($filters as $filter => $value) {
            if(!empty($value)) {
                $filteredTable->where($filter,'like',$value);
            }
        }
        $resultsSize = $filteredTable->count();

        $rows = $filteredTable->select($external_fields)->take($MAX_SIZE)->get();
        $records = $recordType->records;
        $map = [];
        foreach ($records as $record) {
            /** @var Record $record */
            $key = $record->getLocal($ext['local_key']);
            if (isset ($key)) {
                $map[$key] = $record;
            }
        }


        foreach ($rows as $row) {
            $keyname = $ext['key'];
            if (property_exists($row, $keyname)) {
                $key = $row->$keyname;
                if (array_key_exists($key, $map)) {
                    $row->_record = $map[$key];
                } else {
                    $row->_create = $this->linkMaker->url(
                        $recordType,
                        'create-record',
                        [
                            "field_" . $ext['local_key'] => $key,
                            "_mmreturn" => $this->linkMaker->url(
                                $recordType,
                                "external-records",
                                $this->requestProcessor->all())
                        ]
                    );
                }
            }
        }

        return view('recordType.externalRecords', [
            "recordType" => $recordType,
            "columns" => $external_fields,
            "rows" => $rows,
            "totalCount" => $size,
            "resultsCount" => $resultsSize,
            "maxSize" => $MAX_SIZE,
            "filters" => $filters,
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)]);
    }


    /**
     * Display the form for creating a new record of this type.
     *
     * @param RecordType $recordType
     * @return Response
     */
    public function createRecord(RecordType $recordType)
    {
        $this->authorize('create', $recordType);

        $returnLink = $this->requestProcessor->returnURL();

        $dataChanges = $this->requestProcessor->fromFieldsRequest($recordType->fields(), "field_");
        $linkChanges = $this->requestProcessor->getLinkChanges($recordType);

        $record = new Record();
        $record->documentRevision()->associate($recordType->documentRevision);
        $record->record_type_sid = $recordType->sid;
        $record->updateData($dataChanges);

        return view('record.create', [
            "record" => $record,
            "idPrefix" => "",
            "returnTo" => $returnLink,
            "linkChanges" => $linkChanges,
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)
        ]);
    }

    /**
     * Store a newly created record  in storage.
     *
     * @param RecordType $recordType
     * @return RedirectResponse
     * @throws Exception
     */
    public function storeRecord(RecordType $recordType)
    {
        $this->authorize('create', $recordType);

        $action = $this->requestProcessor->get("_mmaction", "");

        if ($action == "cancel") {
            $returnLink = $this->requestProcessor->returnURL(); // no default return link at this stage.
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }

        $dataChanges = $this->requestProcessor->fromFieldsRequest($recordType->fields(), "field_");

        $record = new Record();
        $record->documentRevision()->associate($recordType->documentRevision);
        $record->record_type_sid = $recordType->sid;
        $record->updateData($dataChanges);
        try {
            // validate changes to fields
            $record->validate();
            $linkChanges = $this->requestProcessor->getLinkChanges($recordType);
            $record->validateLinkChanges($linkChanges);
        } catch (Exception $exception) {
            return Redirect::to($this->linkMaker->url($recordType, 'create-record'))
                ->withInput()
                ->withErrors($exception->getMessage());
        }

        $record->save();

        $record->applyLinkChanges($linkChanges);

        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($record));

        return Redirect::to($returnLink)
            ->with("message", "Record created.");
    }


}
