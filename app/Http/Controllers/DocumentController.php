<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Auth;
use Exception;
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
        $this->authorize('view-published-latest', $document);

        if (!Auth::user()->can('view-draft', $document)
            && !Auth::user()->can('view-archive', $document)
        ) {
            // if we can only see the published revisions then redirect to the latest of those
            return $this->latestPublished($document);
        }

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
    public function latestPublished(Document $document)
    {
        $this->authorize('view-published-latest', $document);

        $latest = $document->latestPublishedRevision();
        if (!$latest) {
            return Redirect::to($this->linkMaker->url($document))
                ->withErrors("This document does not currently have a public revision.");
        }
        return Redirect::to($this->linkMaker->url($latest));
    }

    /**
     * Display the specified resource.
     * @param Document $document
     * @return RedirectResponse
     */
    public function latest(Document $document)
    {
        $this->authorize('view-archive', $document);

        $latestPublic = $document->latestRevision();
        return Redirect::to($this->linkMaker->url($latestPublic));
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


    /**
     * Show the form for making a new draft revision
     *
     * @param Document $document
     * @return Response
     */
    public function makeDraftForm(Document $document)
    {
        $this->authorize('publish', $document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentNavigation($document),
            "actionLabel" => "Make new draft based on latest revision",
            "subjectLabel" => $this->titleMaker->title($document),
            "action" => $this->linkMaker->url($document, "create-draft")
        ]);
    }

    /**
     * Process the form for a new draft revision
     *
     * @param Document $document
     * @return RedirectResponse
     */
    public function makeDraft(Document $document)
    {
        $this->authorize('publish', $document);

        $action = $this->requestProcessor->get("_mmaction", "");
        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($document));

        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        // if action is not cancel it's treated as confirmation

        $draft = null;
        try {
            $draft = $document->createDraftRevision();
        } catch (Exception $exception) {
            return Redirect::to($returnLink)
                ->withErrors($exception->getMessage());
        }

        // apply changes to links
        return Redirect::to($this->linkMaker->url($draft))
            ->with("message", "Created new draft from latest revision");
    }

}
