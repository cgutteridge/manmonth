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
                "record" => $record
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
        $record = new Record();
        $record->documentRevision()->associate($recordType->documentRevision);
        $record->record_type_sid = $recordType->sid;
        $record->updateData($this->requestProcessor->fromOldRequest($request, $record->recordType->fields()));

        return view('record.create', [
            "record" => $record,
            "idPrefix" => "",
            "returnTo" => $request->get("_mmreturn", ""),
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
        $returnLink = $request->get("_mmreturn", $this->linkMaker->url($recordType, "records"));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }
        $record = new Record();
        $record->documentRevision()->associate($recordType->documentRevision);
        $record->record_type_sid = $recordType->sid;
        $record->updateData($this->requestProcessor->fromRequest($request, $recordType->fields()));

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
