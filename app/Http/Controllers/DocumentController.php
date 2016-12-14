<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Redirect;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $list = Document::all()->reverse();
        return view('document.index', [
            "list" => $list,
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // TODO
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
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
     * @param Document $document
     * @return Response
     */
    public function show(Document $document)
    {
        $this->authorize('view-current', $document);

        return view('document.show', [
            'document' => $document,
            'nav' => $this->navigationMaker->documentNavigation($document)
        ]);
    }

    /**
     * Display the specified resource.
     * @param Document $document
     * @return RedirectResponse
     */
    public function current(Document $document)
    {
        $this->authorize('view-current', $document);

        $current = $document->currentRevision();
        return Redirect::to($this->linkMaker->url($current));
    }

    /**
     * Display the specified resource.
     * @param Document $document
     * @return RedirectResponse
     */
    public function draft(Document $document)
    {
        $this->authorize('view-draft', $document);

        $draft = $document->draftRevision();
        if (!$draft) {
            return Redirect::to($this->linkMaker->url($document))
                ->withErrors("This document does not currently have a draft revision.");
        }
        return Redirect::to($this->linkMaker->url($draft));
    }
}
