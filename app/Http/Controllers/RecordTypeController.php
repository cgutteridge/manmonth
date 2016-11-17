<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\RecordType;
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
     * Display the form for creating a new record of this type.
     *
     * @param RecordType $recordType
     * @return Response
     */
    public function createRecord(RecordType $recordType)
    {
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
        // TODO different returnTo for cancel to success?
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
        $action = $this->requestProcessor->get("_mmaction", "");

        if ($action == "cancel") {
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


    // TODO : other methods
}
