<?php

namespace App\Http\Controllers;

use App\Models\DocumentRevision;
use Illuminate\Http\Request;

class DocumentRevisionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param DocumentRevision $revision
     * @return \Illuminate\Http\Response
     */
    public function show(DocumentRevision $revision)
    {
        return view('documentRevision.show', [
            "documentRevision" => $revision,
            'nav' => $this->navigationMaker->documentRevisionNavigation($revision)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }
}
