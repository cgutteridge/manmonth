<?php

namespace App\Http\Controllers;

use App\Exceptions\MMValidationException;
use App\Fields\Field;
use App\Models\DocumentRevision;
use Auth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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
     * @throws AuthorizationException
     */
    public function show(DocumentRevision $documentRevision)
    {
        $this->authorize('view', $documentRevision);
        $latestPublished = $documentRevision->document->latestPublishedRevision;
        $latest = $documentRevision->document->latestRevision;

        return view('documentRevision.show', [
            "documentRevision" => $documentRevision,
            "status" => $documentRevision->status,
            "created_at" => $documentRevision->created_at,
            "published" => $documentRevision->published,
            "comment" => $documentRevision->comment,
            "user" => ($documentRevision->user ? $documentRevision->user->name : null),
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
     * @throws MMValidationException
     * @throws AuthorizationException
     */
    public function publishForm(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Publish",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "publish"),
            "formFields" => [
                "idPrefix" => "",
                "fields" => DocumentRevisionController::editableFields(),
                "values" => $documentRevision->toArray(),
            ]
        ]);
    }

    /**
     * Respond to the commit form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function publishAction(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->publish();
                $this->setFieldsFromRequest($documentRevision);
            },
            "Revision published."
        );
    }

    /**
     * Show the form for commiting this revision
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws MMValidationException
     * @throws AuthorizationException
     */
    public function commitForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Commit",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "commit"),
            "formFields" => [
                "idPrefix" => "",
                "fields" => DocumentRevisionController::editableFields(),
                "values" => $documentRevision->toArray(),
            ]
        ]);
    }

    /**
     * Respond to the commit form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function commitAction(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->commit();
                $this->setFieldsFromRequest($documentRevision);
            },
            "Revision committed."
        );
    }

    /**
     * Show the form for commiting this revision and publishing it.
     *
     * @param DocumentRevision $documentRevision
     * @return Response
     * @throws MMValidationException
     * @throws AuthorizationException
     */
    public function commitAndPublishForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);
        $this->authorize('publish', $documentRevision->document);


        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Commit and publish revision",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "commit-and-publish"),
            "returnTo" => $this->linkMaker->url($documentRevision->document),
            "formFields" => [
                "idPrefix" => "",
                "fields" => DocumentRevisionController::editableFields(),
                "values" => $documentRevision->toArray(),
            ]
        ]);
    }

    /**
     * Respond to the commit-and-publish form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function commitAndPublishAction(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);
        $this->authorize('publish', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->commit();
                $documentRevision->publish();
                $this->setFieldsFromRequest($documentRevision);
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
     * @throws MMValidationException
     * @throws AuthorizationException
     */
    public function commitAndContinueForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Commit and make a new draft revision",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "commit-and-continue"),
            "returnTo"=>$this->linkMaker->url($documentRevision->document),
            "formFields" => [
                "idPrefix" => "",
                "fields" => DocumentRevisionController::editableFields(),
                "values" => $documentRevision->toArray(),
            ]
        ]);
    }

    /**
     * Respond to the commit-and-continue form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function commitAndContinueAction(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->commit();
                $this->setFieldsFromRequest($documentRevision);
                $newRevision = $documentRevision->document->createDraftRevision(Auth::user());
                $this->setFieldsFromRequest($newRevision);
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
     * @throws MMValidationException
     * @throws AuthorizationException
     */
    public function unpublishForm(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Unpublish",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url($documentRevision, "unpublish"),
            "formFields" => [
                "idPrefix" => "",
                "fields" => DocumentRevisionController::editableFields(),
                "values" => $documentRevision->toArray(),
            ]
        ]);
    }

    /**
     * Respond to the publish form.
     *
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function unpublishAction(DocumentRevision $documentRevision)
    {
        $this->authorize('publish', $documentRevision->document);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->unpublish();
                $this->setFieldsFromRequest($documentRevision);
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
     * @throws MMValidationException
     * @throws AuthorizationException
     */
    public function scrapForm(DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);

        return view('confirmForm', [
            'nav' => $this->navigationMaker->documentRevisionNavigation($documentRevision),
            "actionLabel" => "Scrap draft revision",
            "subjectLabel" => $this->titleMaker->title($documentRevision),
            "action" => $this->linkMaker->url(
                $documentRevision,
                "scrap"
            ),
            "formFields" => [
                "idPrefix" => "",
                "fields" => DocumentRevisionController::editableFields(),
                "values" => $documentRevision->toArray(),
            ]
        ]);
    }

    /**
     * Respond to the scrap form
     *
     * @param Request $request
     * @param DocumentRevision $documentRevision
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function scrapAction(Request $request, DocumentRevision $documentRevision)
    {
        $this->authorize('commit-revision', $documentRevision);

        return $this->genericAction(
            $documentRevision,
            function (DocumentRevision $documentRevision) {
                $documentRevision->scrap();
                $this->setFieldsFromRequest($documentRevision);
            },
            "Revision scrapped.",
            $this->linkMaker->url($documentRevision->document)
        );
    }


    /**
     * Read the fields from the form and apply them. This should mirror editableFields()
     * @param DocumentRevision $documentRevision
     */
    public function setFieldsFromRequest(DocumentRevision $documentRevision)
    {
        $documentRevision->comment = $this->requestProcessor->get("comment");
        $documentRevision->save();
    }

    /**
     * Returns fields that can be edited on a revision.
     * @return array
     */
    public static function editableFields()
    {
        try {
            return [Field::createFromData([
                "type" => "string",
                "name" => "comment",
                "label" => "Comment"])];
        } catch (Exception $e) {
            return [];
        }
    }

}