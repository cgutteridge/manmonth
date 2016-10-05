<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Redirect;

class RecordController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param Record $record
     * @return Response
     */
    public function show(Record $record)
    {
        $reports = [];
        foreach ($record->recordType->reportTypes as $reportType) {
            $reports [] = $reportType->recordReport($record);
        }

        return view('record.show', [
            "record" => $record,
            "reports" => $reports,
            "nav" => $this->navigationMaker->documentRevisionNavigation($record->documentRevision)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param Record $record
     * @return Response
     */
    public function edit(Request $request, Record $record)
    {
        $record->updateData($this->requestProcessor->fromOldRequest($request, $record->recordType->fields()));
        return view('record.edit', [
            "record" => $record,
            "idPrefix" => "",
            "returnTo" => $request->get("_mmreturn", ""),
            "nav" => $this->navigationMaker->documentRevisionNavigation($record->documentRevision)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Record $record
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(Request $request, Record $record)
    {
        $action = $request->get("_mmaction", "");
        $returnLink = $request->get("_mmreturn", $this->linkMaker->link($record));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }
        $record->updateData(
            $this->requestProcessor->fromRequest($request, $record->recordType->fields()));
        try {
            $record->validate();
        } catch (Exception $exception) {
            return Redirect::to('records/' . $record->id . "/edit")
                ->withInput()
                ->withErrors($exception->getMessage());
        }
        $record->save();
        return Redirect::to($returnLink)
            ->with("message", "Record updated.");
    }
}
