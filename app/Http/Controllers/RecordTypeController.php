<?php

namespace App\Http\Controllers;

use App\Models\RecordType;
use Response;

class RecordTypeController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param RecordType $recordtype
     * @return Response
     */
    public function show(RecordType $recordtype)
    {
        return view('recordType.show', ["recordtype" => $recordtype]);
    }

}
