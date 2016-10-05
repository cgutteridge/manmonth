<?php

namespace App\Http\Controllers;

use App\Models\DocumentRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentRevisionController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     */
    public function show(DocumentRevision $documentRevision)
    {
        return view('documentRevision.show', [
            "documentRevision" => $documentRevision,
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision)
        ]);
    }

    /**
     * Show the form for publishing this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     */
    public function publishForm(DocumentRevision $documentRevision)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Respond to the publish form.
     *
     * @param Request $request
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     */
    public function publishAction(Request $request, DocumentRevision $documentRevision)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Show the form for scrapping this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     */
    public function scrapForm(DocumentRevision $documentRevision)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Respond to the scrap form.
     *
     * @param Request $request
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     */
    public function scrapAction(Request $request, DocumentRevision $documentRevision)
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }
}
