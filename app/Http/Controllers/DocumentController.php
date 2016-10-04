<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Display the specified resource.
     * @param Document $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        return view('document.show', [
            'document' => $document,
            'nav' => $this->navigationMaker->documentNavigation($document)
        ]);
    }

    /**
     * Display the specified resource.
     * @param Document $document
     * @return \Illuminate\Http\Response
     */
    public function current(Document $document)
    {
        $current = $document->currentRevision();
        return Redirect::to($this->linkMaker->link($current));
    }

    /**
     * Display the specified resource.
     * @param Document $document
     * @return \Illuminate\Http\Response
     */
    public function draft(Document $document)
    {
        $draft = $document->draftRevision();
        if (!$draft) {
            return Redirect::to($this->linkMaker->link($document))
                ->withErrors("This document does not currently have a draft revision.");
        }
        return Redirect::to($this->linkMaker->link($draft));
    }
}
