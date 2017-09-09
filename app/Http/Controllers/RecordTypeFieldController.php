<?php

namespace App\Http\Controllers;

use App\Exceptions\MMValidationException;
use App\Models\RecordType;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Redirect;

class RecordTypeFieldController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param RecordType $recordType
     * @param string $fieldName
     * @return View
     */
    public function edit(RecordType $recordType, $fieldName)
    {
        $this->authorize('edit', $recordType);

        $field = $recordType->field($fieldName);
        if ($field == null) {
            abort(404, 'Unknown field.');
        }

        $fieldChanges = $this->requestProcessor->fromFieldsRequest(
            $field->metaFields(), "field_");
        $field->updateData($fieldChanges);

        $info = [
            "recordType" => $recordType,
            "field" => $field,
            "meta" => [
                "idPrefix" => "field_",
                "fields" => $field->metaFields(),
                "values" => $field->data
            ],
            "returnTo" => $this->requestProcessor->returnURL(),
            "nav" => $this->navigationMaker->recordTypeNavigation($recordType)
        ];
        return view('recordType.fieldEdit', $info);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RecordType $recordType
     * @param string $fieldName
     * @return RedirectResponse
     * @throws Exception
     */
    public
    function update(RecordType $recordType, $fieldName)
    {
        $this->authorize('edit', $recordType);

        $field = $recordType->field($fieldName);
        if ($field == null) {
            abort(404, 'Unknown field.');
        }

        $this->authorize('edit', $recordType);

        $action = $this->requestProcessor->get("_mmaction", "");
        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($recordType));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }
        $dataChanges = $this->requestProcessor->fromFieldsRequest($field->metaFields(), "field_");
        $field->updateData($dataChanges);
        $recordType->setField($field);

        try {
            // validate changes to fields
            $recordType->validate();
        } catch (MMValidationException $exception) {
            return Redirect::to($this->linkMaker->url($field, "edit"))
                ->withInput()
                ->withErrors($exception->getMessage());
        }

        // apply changes to links
        $recordType->save();

        return Redirect::to($returnLink)
            ->with("message", "Record schema updated.");
    }

}
