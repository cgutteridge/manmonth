<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\RequestProcessor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Redirect;

class RecordController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param RequestProcessor $requestProcessor
     * @param  Request $request
     * @return Response
     */
    public function store(RequestProcessor $requestProcessor, Request $request)
    {
        $record = new Record();
        $record->updateData($requestProcessor->fromRequest($request, $record->recordType->fields()));
        try {
            $record->validateData();
        } catch (Exception $exception) {
            return Redirect::to('records/' . $record->id . "/edit")
                ->withInput()
                ->withErrors($exception->getMessage());
        }
        $record->save();
        return Redirect::to('records/' . $record->id)
            ->with("message", "Record updated.");
    }

    /**
     * Display the specified resource.
     *
     * @param Record $record
     * @return Response
     */
    public function show(Record $record)
    {
        return view('record.show', ["record" => $record]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param RequestProcessor $requestProcessor
     * @param Record $record
     * @return Response
     */
    public function edit(Request $request, RequestProcessor $requestProcessor, Record $record)
    {
        $record->updateData($requestProcessor->fromOldRequest($request, $record->recordType->fields()));
        return view('record.edit', ["record" => $record, "idPrefix" => ""]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param RequestProcessor $requestProcessor
     * @param Record $record
     * @return Response
     * @internal param int $id
     */
    public function update(Request $request, RequestProcessor $requestProcessor, Record $record)
    {
        $record->updateData($requestProcessor->fromRequest($request, $record->recordType->fields()));
        try {
            $record->validateData();
        } catch (Exception $exception) {
            return Redirect::to('records/' . $record->id . "/edit")
                ->withInput()
                ->withErrors($exception->getMessage());
        }
        $record->save();
        return Redirect::to('records/' . $record->id)
            ->with("message", "Record updated.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        // delete attached links and N-1 records too?
    }
}
