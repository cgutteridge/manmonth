<?php

namespace App\Http\Controllers;

use App\Models\RecordType;
use Response;

class RecordTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param RecordType $record_type
     * @return Response
     */
    public function show(RecordType $record_type)
    {
        return view('recordType.show', [
            "recordType" => $record_type,
            "nav" => $this->navigationMaker->documentRevisionNavigation($record_type->documentRevision)]);
    }

    // TODO : other methods
}
