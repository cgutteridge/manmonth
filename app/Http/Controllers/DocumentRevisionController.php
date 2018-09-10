<?php

namespace App\Http\Controllers;

use App\Models\DocumentRevision;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Redirect;

class DocumentRevisionController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(DocumentRevision $documentRevision)
    {
        $this->authorize('view', $documentRevision);
        $latestPublished = $documentRevision->document->latestPublishedRevision();
        $latest = $documentRevision->document->latestRevision();

        return view('documentRevision.show', [
            "documentRevision" => $documentRevision,
            "status" => $documentRevision->status,
            "created_at" => $documentRevision->created_at,
            "published" => $documentRevision->published,
            "latest" => isset($latest) && $documentRevision->id == $latest->id,
            "latest_published" => isset($latestPublished) && $documentRevision->id == $latestPublished->id,
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision)
        ]);
    }

    /**
     * Respond to the publish form.
     *
     * @param DocumentRevision $documentRevision
     * @param $actionFunction
     * @param string $successMessage
     * @param null|string $successURL
     * @return RedirectResponse
     * @throws Exception
     */
    public function genericAction(DocumentRevision $documentRevision, $actionFunction, $successMessage, $successURL = null)
    {
        $action = $this->requestProcessor->get("_mmaction", "");
        $returnLink = $this->requestProcessor->returnURL($this->linkMaker->url($documentRevision));

        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        // if action is not cancel it's treated as confirmation

        try {
            $actionFunction($documentRevision);
        } catch (Exception $exception) {
            return Redirect::to($returnLink)
                ->withErrors($exception->getMessage());
        }

        if (!empty($successURL)) {
            $returnLink = $successURL;
        }

        // apply changes to links
        return Redirect::to($returnLink)
            ->with("message", $successMessage);
    }

    /**
     * Show the form for commiting this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function publishForm(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Publish",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "publish")
        ]);
    }

    /**
     * Respond to the commit form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function publishAction(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->publish();
            },
            "Revision published."
        );
    }

    /**
     * Show the form for commiting this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function commitForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Commit",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "commit")
        ]);
    }

    /**
     * Respond to the commit form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function commitAction(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->commit();
            },
            "Revision committed."
        );
    }

    /**
     * Show the form for commiting this revision and publishing it.
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function commitAndPublishForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);
        $this->authorize('publish', $documentRevision->document);


        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Commit and publish revision",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "commit-and-publish"),
            $this->linkMaker->url($documentRevision->document)
        ]);
    }

    /**
     * Respond to the commit-and-publish form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function commitAndPublishAction(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);
        $this->authorize('publish', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->commit();
                $documentRevision->publish();
            },
            "Revision committed and published.",
            $this->linkMaker->url($documentRevision->document)
        );
    }

    /**
     * Show the form for commiting this revision and making a new one to edit..
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function commitAndContinueForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Commit and make a new draft revision",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "commit-and-continue"),
            $this->linkMaker->url($documentRevision->document)
        ]);
    }

    /**
     * Respond to the commit-and-continue form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function commitAndContinueAction(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->commit();
                $documentRevision->document->createDraftRevision();
            },
            "Revision committed and new draft created.",
            $this->linkMaker->url($documentRevision->document, 'draft')
        );
    }


    /**
     * Show the form for publishing this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unpublishForm(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Unpublish",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "unpublish")
        ]);
    }

    /**
     * Respond to the publish form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unpublishAction(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->unpublish();
            },
            "Revision unpublished.",
            $this->linkMaker->url($documentRevision->document)
        );
    }

    /**
     * Show the form for scrapping this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function scrapForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Scrap draft revision",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url(
                $documentRevision,
                "scrap"
            )
        ]);
    }

    /**
     * Respond to the scrap form
     *
     * @param Request $request
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function scrapAction(Request $request, DocumentRevision $documentRevision)
    {
        $this->authorize('commit', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->scrap();
            },
            "Revision scrapped.",
            $this->linkMaker->url($documentRevision->document)
        );

    }
}
