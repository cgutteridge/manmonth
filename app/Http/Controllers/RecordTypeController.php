<?php

namespace App\Http\Controllers;

use App\Exceptions\DataStructValidationException;
use App\Models\Record;
use App\Models\RecordType;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @param RecordType $recordType
     * @return Response
     */
    public function createRecord(Request $request, RecordType $recordType)
    {
        $mmReturn = $this->requestProcessor->returnURL($request);

        $record = new Record();
        $record->documentRevision()->associate($recordType->documentRevision);
        $record->record_type_sid = $recordType->sid;
        $record->updateData($this->requestProcessor->fromOldFieldsRequest($request, $record->recordType->fields(), "field_"));

        return view('record.create', [
            "record" => $record,
            "idPrefix" => "",
            "returnTo" => $mmReturn,
            "nav" => $this->navigationMaker->documentRevisionNavigation($recordType->documentRevision)
        ]);
        // TODO different returnTo for cancel to success?
    }

    /**
     * Store a newly created record  in storage.
     *
     * @param  Request $request
     * @param RecordType $recordType
     * @return RedirectResponse
     * @throws Exception
     */
    public function storeRecord(Request $request, RecordType $recordType)
    {
        $action = $request->get("_mmaction", "");
        $mmReturn = $this->requestProcessor->returnURL($request);

        $returnLink = $mmReturn;
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }
        $record = new Record();
        $record->documentRevision()->associate($recordType->documentRevision);
        $record->record_type_sid = $recordType->sid;
        $record->updateData($this->requestProcessor->fromFieldsRequest($request, $recordType->fields(), "field_"));

        try {
            $record->validate();
        } catch (DataStructValidationException $exception) {
            return Redirect::to($this->linkMaker->url($recordType, 'create-record'))
                ->withInput()
                ->withErrors($exception->getMessage());
        }
        $record->save();
        return Redirect::to($this->linkMaker->url($record))
            ->with("message", "Record created.");
    }


    // TODO : other methods
}
