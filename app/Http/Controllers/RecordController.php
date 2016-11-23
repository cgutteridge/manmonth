<?php

namespace App\Http\Controllers;

use App\Exceptions\MMValidationException;
use App\Exceptions\ReportingException;
use App\Models\Record;
use App\Models\ReportType;
use Exception;
use Illuminate\Http\RedirectResponse;
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
        $errors = [];
        $reports = [];
        foreach ($record->recordType->reportTypes as $reportType) {
            /** @var ReportType $reportType */
            try {
                $reports [] = $reportType->recordReport($record);
            } catch (ReportingException $e) {
                $errors [] = $e->getMessage();
            }
        }

        return view('record.show', [
            "record" => $record,
            "recordBlock" => $this->recordBlock($record, 'all', [], $this->linkMaker->url($record), true),
            "reports" => $reports,
            "nav" => $this->navigationMaker->documentRevisionNavigation($record->documentRevision)
        ])->withErrors($errors);
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
                        // a forward link means the target will have a backlink
                        // to this record.
                        $createLink = $this->linkMaker->url($linkType->range, "create-record", [
                            "link_bck_" . $linkType->sid . "_add_" . $record->sid => 1,
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
                        // a back link means the target will have a fwdlink
                        // to this record.
                        // link_fwd_2_add_12
                        $createLink = $this->linkMaker->url($linkType->domain, "create-record", [
                            "link_fwd_" . $linkType->sid . "_add_" . $record->sid => 1,
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
     * @param Record $record
     * @return Response
     */
    public function edit(Record $record)
    {
        $fieldChanges = $this->requestProcessor->fromFieldsRequest(
            $record->recordType->fields(), "field_");
        $linkChanges = $this->requestProcessor->getLinkChanges(
            $record->recordType);
        $record->updateData($fieldChanges);
        return view('record.edit', [
            "record" => $record,
            "idPrefix" => "",
            "returnTo" => $this->requestProcessor->returnURL(),
            "linkChanges" => $linkChanges,
            "nav" => $this->navigationMaker->documentRevisionNavigation($record->documentRevision)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Record $record
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(Record $record)
    {
        $action = $this->requestProcessor->get("_mmaction", "");
        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($record));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }

        $dataChanges = $this->requestProcessor->fromFieldsRequest($record->recordType->fields(), "field_");
        $record->updateData($dataChanges);
        try {
            // validate changes to fields
            $record->validate();
            $linkChanges = $this->requestProcessor->getLinkChanges($record->recordType);
            $record->validateLinkChanges($linkChanges);
        } catch (MMValidationException $exception) {
            return Redirect::to('records/' . $record->id . "/edit")
                ->withInput()
                ->withErrors($exception->getMessage());
        }

        // apply changes to links
        $record->save();

        $record->applyLinkChanges($linkChanges);

        return Redirect::to($returnLink)
            ->with("message", "Record updated.");
    }

}
