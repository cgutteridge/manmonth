<?php

namespace App\Http\Controllers;

use App\Exceptions\MMValidationException;
use App\Models\Record;
use App\Models\RecordType;
use DB;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Redirect;
use View;


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

        $pageinfo = [];
        $pageinfo["recordType"] = $recordType;

        $pageinfo["meta"] = [
            "fields" => $recordType->metaFields(),
            "values" => $recordType->metaValues()
        ];

        $pageinfo["nav"] = $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision);
        $pageinfo["hasExternalLink"] = $recordType->isLinkedToExternalData();
        if ($recordType->isLinkedToExternalData()) {
            $pageinfo["externalLink"] = [
                "table" => $recordType->external_table,
                "key" => $recordType->external_key,
                "local_key" => $recordType->external_local_key
            ];
        }

        $pageinfo["fields"] = [];
        foreach ($recordType->fields() as $field) {
            $pageinfo["fields"][] = [
                "title" => $this->titleMaker->title($field),
                "edit" => $this->linkMaker->url($field, "edit"),
                "fields" => $field->metaFields(),
                "values" => $field->data
            ];
        }
        return view('recordType.show', $pageinfo);
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
        foreach ($recordType->records() as $record) {
            # create a sort key. Add ID to the end so we don't lose items with the
            # same name.
            $sortKey = strtoupper($this->titleMaker->title($record)) . "#" . $record->sid;
            $recordBlocks[$sortKey] = [
                "data" => $recordController->recordDataBlock($record),
                "links" => [],
                "returnURL" => $this->linkMaker->url($recordType, "records"),
                "record" => $record,
                "swimLanes" => false
            ];
        }
        ksort($recordBlocks);
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
        $this->authorize('create', $recordType);

        $issues = $this->externalRecordsIssues($recordType);
        if (count($issues)) {
            return Redirect::to($this->linkMaker->url($recordType))
                ->withErrors($issues);
        }

        $filteredTable = $this->externalRecordsFilteredTable($recordType);

        // This is a value of the rows matching, the actual number
        // may be smaller because it only selects some columns and with
        // a DISTINCT.
        $resultsSize = $filteredTable->count();
        $rows = $filteredTable->take($MAX_SIZE)->get();

        /*
         * $map is a list of records indexed by the value that links
         * them to the import table (external_local_key)
        */
        $map = $this->recordsByLocalKey($recordType);

        /* set up the URLs to create or view the record described
         * by each row of the database.
         */
        foreach ($rows as $row) {
            $keyname = $recordType->external_key;
            if (property_exists($row, $keyname)) {
                $key = $row->$keyname;
                if (array_key_exists($key, $map)) {
                    $row->_record = $map[$key];
                } else {
                    $row->_create = $this->linkMaker->url(
                        $recordType,
                        'create-record',
                        [
                            "field_" . $recordType->external_local_key => $key,
                            "_mmreturn" => $this->linkMaker->url(
                                $recordType,
                                "external-records",
                                $this->requestProcessor->all())
                        ]
                    );
                }
            }
        }

        $tableSize = $this->externalRecordsTable($recordType)->count();

        return view('recordType.externalRecords', [
            "recordType" => $recordType,
            "columns" => $recordType->externalColumns(),
            "rows" => $rows,
            "totalCount" => $tableSize,
            "resultsCount" => $resultsSize,
            "maxSize" => $MAX_SIZE,
            "filters" => $this->requestProcessor->filters(),
            "importUrl" => $this->linkMaker->url($recordType, 'external-records-bulk-import', $this->requestProcessor->all()),
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)]);
    }

    /**
     * @param RecordType $recordType
     * @return array
     * Returns a list of issues that would prevent the bulk importer
     * working on this record type.
     */
    private function externalRecordsIssues(RecordType $recordType)
    {
        $external_fields = $recordType->externalColumns();

        $issues = [];
        if (count($external_fields) == 0) {
            $issues [] = "External data not configured for this type of record.";
        }
        return $issues;
    }

    /**
     * @param RecordType $recordType
     * @return Builder
     */
    private function externalRecordsFilteredTable(RecordType $recordType)
    {
        $filters = $this->requestProcessor->filters();
        $filteredTable = $this->externalRecordsTable($recordType);
        foreach ($filters as $filter => $value) {
            if (!empty($value)) {
                $filteredTable->where($filter, 'like', $value);
            }
        }
        $external_fields = $recordType->externalColumns();
        $filteredTable->select($external_fields);
        return $filteredTable;
    }

    /**
     * @param RecordType $recordType
     * @return Builder
     */
    private function externalRecordsTable(RecordType $recordType)
    {
        $tableName = 'imported_' . $recordType->external_table;
        return DB::table($tableName)->distinct();
    }

    private function recordsByLocalKey(RecordType $recordType)
    {
        $map = [];
        foreach ($recordType->records() as $record) {
            /** @var Record $record */
            $key = $record->getLocal($recordType->external_local_key);
            if (isset ($key)) {
                $map[$key] = $record;
            }
        }
        return $map;
    }

    /**
     * Serve a continue/cancel form before doing a bulk import.
     *
     * @param RecordType $recordType
     * @return Response
     */
    public function bulkImportConfirm(RecordType $recordType)
    {
        $this->authorize('create', $recordType);

        $issues = $this->externalRecordsIssues($recordType);
        if (count($issues)) {
            return Redirect::to($this->linkMaker->url($recordType))
                ->withErrors($issues);
        }

        $filteredTable = $this->externalRecordsFilteredTable($recordType);

        $rows = $filteredTable->get();

        /*
         * $map is a list of records indexed by the value that links
         * them to the import table (external_local_key)
        */
        $map = $this->recordsByLocalKey($recordType);

        /* set up the URLs to create or view the record described
         * by each row of the database.
         */
        $toImportCount = 0;
        $wontImportCount = 0;
        foreach ($rows as $row) {
            $keyname = $recordType->external_key;
            if (property_exists($row, $keyname)) {
                $key = $row->$keyname;
                if (array_key_exists($key, $map)) {
                    $wontImportCount++;
                } else {
                    $toImportCount++;
                }
            }
        }

        return view('recordType.bulkImportConfirm', [
            "recordType" => $recordType,
            "wontImportCount" => $wontImportCount,
            "toImportCount" => $toImportCount,
            "filters" => $this->requestProcessor->filters(),
            "importUrl" => $this->linkMaker->url($recordType, 'external-records-bulk-import'),
            "cancelUrl" => $this->linkMaker->url($recordType, 'external-records', $this->requestProcessor->all()),
            "importUrlParams" => $this->requestProcessor->all(),
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)]);
    }


    /**
     * Actually do a bulk import.
     *
     * @param RecordType $recordType
     * @return Response
     */
    public function bulkImport(RecordType $recordType)
    {
        $this->authorize('create', $recordType);

        $issues = $this->externalRecordsIssues($recordType);
        if (count($issues)) {
            return Redirect::to($this->linkMaker->url($recordType))
                ->withErrors($issues);
        }

        $filteredTable = $this->externalRecordsFilteredTable($recordType);

        $rows = $filteredTable->get();

        /*
         * $map is a list of records indexed by the value that links
         * them to the import table (external_local_key)
        */
        $map = $this->recordsByLocalKey($recordType);

        $returnLink = $this->requestProcessor->returnURL();

        /* set up the URLs to create or view the record described
         * by each row of the database.
         */
        $importCount = 0;
        foreach ($rows as $row) {
            $keyname = $recordType->external_key;
            if (property_exists($row, $keyname)) {
                $key = $row->$keyname;
                if (array_key_exists($key, $map)) {
                    continue;
                }

                $record = new Record();
                $record->documentRevision()->associate($recordType->documentRevision);
                $record->record_type_sid = $recordType->sid;
                # force the $key to be a string not an integer
                $dataChanges = [$recordType->external_local_key => "$key"];

                $record->updateData($dataChanges);
                try {
                    // validate changes to fields
                    $record->validate();
                    $linkChanges = $this->requestProcessor->getLinkChanges($recordType);
                    $record->validateLinkChanges($linkChanges);
                } catch (Exception $exception) {
                    /* maybe this should include how many messages were created too? */
                    return Redirect::to($returnLink)
                        ->withInput()
                        ->withErrors($exception->getMessage());
                }
                $record->save();
                $importCount++;
            }
        }

        return Redirect::to($returnLink)
            ->with("message", "Created $importCount record" . ($importCount == 1 ? "" : "s"));
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param RecordType $recordType
     * @return View
     */
    public function edit(RecordType $recordType)
    {
        $this->authorize('edit', $recordType);

        $fieldChanges = $this->requestProcessor->fromFieldsRequest(
            $recordType->metaFields(), "field_");
        $recordType->updateValues($fieldChanges);
        return view('recordType.edit', [
            "recordType" => $recordType,
            "meta" => [
                "idPrefix" => "field_",
                "fields" => $recordType->metaFields(),
                "values" => $recordType->metaValues()
            ],
            "returnTo" => $this->requestProcessor->returnURL(),
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RecordType $recordType
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(RecordType $recordType)
    {
        $this->authorize('edit', $recordType);

        $action = $this->requestProcessor->get("_mmaction", "");
        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($recordType));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }
        $dataChanges = $this->requestProcessor->fromFieldsRequest($recordType->metaFields(), "field_");
        $recordType->updateValues($dataChanges);

        try {
            // validate changes to fields
            $recordType->validate();
        } catch (MMValidationException $exception) {
            $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($recordType));

            return Redirect::to($this->linkMaker->url($recordType, "edit"))
                ->withInput()
                ->withErrors($exception->getMessage());
        }

        // apply changes to links
        $recordType->save();

        return Redirect::to($returnLink)
            ->with("message", "Record schema updated.");
    }

}
