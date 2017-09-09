<?php

namespace App\Http\Controllers;

use App\Exceptions\MMValidationException;
use App\Exceptions\ReportingException;
use App\Fields\Field;
use App\Models\Record;
use App\Models\ReportType;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Redirect;

class RecordController extends Controller
{

    /**
     * Display the form to delete the record.
     *
     * @param Record $record
     * @return View
     */
    public function deleteForm(Record $record)
    {
        $this->authorize('edit', $record);

        $renderErrors = $record->reasonsThisCantBeErased();

        // validate the impact of removing all those links
        return view('record.delete', [
            "record" => $record,
            "renderErrors" => $renderErrors,
            "returnTo" => $this->requestProcessor->returnURL(),
            "nav" => $this->navigationMaker->recordNavigation($record, "Delete")
        ]);
    }

    /**
     * Remove the specified resource from storage, and dependent records and links.
     *
     * @param Record $record
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(Record $record)
    {
        $this->authorize('edit', $record);

        $action = $this->requestProcessor->get("_mmaction", "");
        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($record));
        $recordType = $record->recordType;
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "delete") {
            throw new Exception("Unknown action '$action'");
        }

        try {
            // validate changes to fields
            $record->deleteWithDependencies();
        } catch (Exception $exception) {
            return Redirect::to($returnLink)
                ->withErrors($exception->getMessage());
        }

        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($recordType, "records"));

        // apply changes to links
        return Redirect::to($returnLink)
            ->with("message", "Record and dependencies removed.");
    }

    /**
     * Display the specified resource.
     *
     * @param Record $record
     * @return View
     */
    public function show(Record $record)
    {
        $this->authorize('view', $record);

        $renderErrors = [];
        $reports = [];
        foreach ($record->recordType->reportTypes() as $reportType) {
            /** @var ReportType $reportType */
            try {
                $recordReport = $reportType->recordReport($record);
                $reports [] = [
                    "report" => $recordReport,
                    "categories" => $recordReport->categories()
                ];
            } catch (ReportingException $e) {
                $renderErrors [] = $e->getMessage();
            }
        }

        return view('record.show', [
            "record" => $record,
            "recordBlock" => $this->recordBlock($record, 'all', [], $this->linkMaker->url($record), true),
            "reports" => $reports,
            "renderErrors" => $renderErrors,
            "nav" => $this->navigationMaker->recordNavigation($record)
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
            "followLink" => $followLink,
            "h" => [],
            "record" => $record      // should really pass all the rendered bits instead
        ];
        $seen[$record->id] = true;

        if ($followLink != 'none') {

            foreach ($record->recordType->forwardLinkTypes as $linkType) {
                $block["h"][] = ["dir" => "f", "lt" => $linkType, "dmax" => $linkType->domain_max, "rmax" => $linkType->range_max];
                if ($followLink == 'all'
                    || (isset($linkType->domain_max) && $linkType->domain_max == 1)
                ) {
                    $records = $record->forwardLinkedRecords($linkType);
                    $link = [
                        "title" => $this->titleMaker->title($linkType),
                        "records" => $this->linkedRecords($records, $seen, $returnURL)
                    ];
                    if ($linkType->range_type == 'dependent') {
                        // a forward link means the target will have a backlink
                        // to this record.
                        $link["createLink"] = $this->linkMaker->url($linkType->range(), "create-record", [
                            "link_bck_" . $linkType->sid . "_add_" . $record->sid => 1,
                            "_mmreturn" => $returnURL
                        ]);
                    } else {
                        $link["createLink"] = $this->linkMaker->url($linkType, "create-link", [
                            "subject" => $record->sid,
                            "_mmreturn" => $returnURL
                        ]);
                    }
                    $block["links"][] = $link;
                }
            }
            foreach ($record->recordType->backLinkTypes as $linkType) {
                $block["h"][] = ["dir" => "b", "lt" => $linkType, "dmax" => $linkType->domain_max, "rmax" => $linkType->range_max];

                if ($followLink == 'all'
                    || (isset($linkType->range_max) && $linkType->range_max == 1)
                ) {
                    $records = $record->backLinkedRecords($linkType);

                    $link = [
                        "title" => $this->titleMaker->title($linkType, "inverse"),
                        "records" => $this->linkedRecords($records, $seen, $returnURL)
                    ];
                    if ($linkType->domain_type == 'dependent') {
                        // a back link means the target will have a fwdlink
                        // to this record.
                        // link_fwd_2_add_12
                        $link["createLink"] = $this->linkMaker->url($linkType->domain(), "create-record", [
                            "link_fwd_" . $linkType->sid . "_add_" . $record->sid => 1,
                            "_mmreturn" => $returnURL
                        ]);
                    } else {
                        $link["createLink"] = $this->linkMaker->url($linkType, "create-link", [
                            "object" => $record->sid,
                            "_mmreturn" => $returnURL
                        ]);
                    }
                    $block["links"][] = $link;
                }
            }
        }

        return $block;
    }

    /**
     * Turn a record into a data structure to render with the block template.
     * @param Record $record
     * @return array
     */
    public function recordDataBlock($record)
    {
        $block = [];
        /** @var Field $field */
        foreach ($record->recordType->fields() as $field) {
            $fieldName = $field->data["name"];
            $block[] = [
                "title" => $this->titleMaker->title($field),
                "source" => $record->getSource($fieldName),
                "local" => $record->getLocal($fieldName),
                "external" => $record->getExternal($fieldName),
                "default" => $record->getDefault($fieldName),
                "mode" => $field->getMode(),
                "field" => $field
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
     * @return View
     */
    public function edit(Record $record)
    {
        $this->authorize('edit', $record);

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
            "nav" => $this->navigationMaker->recordNavigation($record, "Edit")
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
        $this->authorize('edit', $record);

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
            return Redirect::to($this->linkMaker->url($record, "edit"))
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
