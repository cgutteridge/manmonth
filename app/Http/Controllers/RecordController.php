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
            "recordBlock" => $this->recordBlock($record, 'all', [], $this->linkMaker->url($record), true),
            "reports" => $reports,
            "nav" => $this->navigationMaker->documentRevisionNavigation($record->documentRevision)
        ]);
    }

    /**
     * @param Record $record
     * @param string $followLink all, single or none
     * @param integer[] $seen
     * @param string $returnURL
     * @param bool $swimLanes if true add linked objects in swim lanes style view
     * @return array
     */
    private function recordBlock(Record $record, $followLink, $seen, $returnURL, $swimLanes = false)
    {
        $block = [
            "data" => $this->recordDataBlock($record),
            "links" => [],
            "returnURL" => $returnURL,
            "swimLanes" => $swimLanes,
            "record" => $record      // should really pass all the rendered bits instead
        ];
        $seen[$record->id] = true;

        if ($followLink != 'none') {
            foreach ($record->recordType->forwardLinkTypes as $linkType) {
                if ($followLink == 'all'
                    || (isset($linkType->range_max) && $linkType->range_max == 1)
                ) {
                    $records = $record->forwardLinkedRecords($linkType);
                    if ($linkType->range_type == 'dependent') {
                        $createLink = $this->linkMaker->url($linkType->range, "create-record", [
                            "subject" => $record->sid,// needs to be clever TODO
                            "_mmreturn" => $returnURL
                        ]);
                    } else {
                        $createLink = $this->linkMaker->url($linkType, "create-link", [
                            "subject" => $record->sid,
                            "_mmreturn" => $returnURL
                        ]);
                    }
                    $block["links"][] = [
                        "title" => $this->titleMaker->title($linkType),
                        "createLink" => $createLink,
                        "records" => $this->linkedRecords($records, $seen, $returnURL)
                    ];
                }
            }
            foreach ($record->recordType->backLinkTypes as $linkType) {
                if ($followLink == 'all'
                    || (isset($linkType->domain_max) && $linkType->domain_max == 1)
                ) {
                    $records = $record->backLinkedRecords($linkType);
                    if ($linkType->domain_type == 'dependent') {
                        $createLink = $this->linkMaker->url($linkType->domain, "create-record", [
                            "object" => $record->sid,// needs to be clever TODO
                            "_mmreturn" => $returnURL
                        ]);
                    } else {
                        $createLink = $this->linkMaker->url($linkType, "create-link", [
                            "object" => $record->sid,
                            "_mmreturn" => $returnURL
                        ]);
                    }
                    $block["links"][] = [
                        "title" => $this->titleMaker->title($linkType, "inverse"),
                        "createLink" => $createLink,
                        "records" => $this->linkedRecords($records, $seen, $returnURL)
                    ];
                }
            }
        }

        return $block;
    }

    /**
     * @param Record $record
     * @return array
     */
    function recordDataBlock($record)
    {
        $block = [];
        foreach ($record->recordType->fields() as $field) {
            $value = null;
            $default = null;
            // this will get more complicated when we have external data sources
            if (array_key_exists($field->data["name"], $record->data)) {
                $value = $record->data[$field->data["name"]];
            }
            if (array_key_exists("default", $field->data)) {
                $default = $field->data["default"];
            }
            $block[] = [
                "title" => $this->titleMaker->title($field),
                "value" => $value,
                "default" => $default
            ];
        }
        return $block;
    }

    /**
     * @param Record[] $records
     * @param array $seen
     * @param string $returnURL
     * @return array
     */
    private function linkedRecords($records, $seen, $returnURL)
    {
        $list = [];
        foreach ($records as $linkedRecord) {
            /*
            if (array_key_exists($linkedRecord->id, $seen)) {
                continue;
            }
            */
            $list[] = $this->recordBlock(
                $linkedRecord,
                "once",
                $seen,
                $returnURL);
        }
        return $list;
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
        $record->updateData($this->requestProcessor->fromOldFieldsRequest($request, $record->recordType->fields(), "field_"));
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
        $returnLink = $this->requestProcessor->returnURL($request, $this->linkMaker->url($record));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }
        $record->updateData(
            $this->requestProcessor->fromFieldsRequest($request, $record->recordType->fields(), "field_"));
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
