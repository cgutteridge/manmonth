<?php

namespace App\Http\Controllers;

use App\Exceptions\MMValidationException;
use App\Models\Link;
use App\Models\LinkType;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Redirect;


class LinkTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param LinkType $linkType
     * @return Response
     */
    public function show(LinkType $linkType)
    {
        $data = [
            "Links from" => [
                "Record type" => $this->titleMaker->title($linkType->domain),
                "Connection type" => $linkType->domain_type,
                "Minimum links" => $linkType->domain_min,
                "Maximum links" => ($linkType->domain_max == 0 ? "Unrestricted" : $linkType->domain_max)
            ],
            "Links to" => [
                "Record type" => $this->titleMaker->title($linkType->range),
                "Connection type" => $linkType->range_type,
                "Minimum links" => $linkType->range_min,
                "Maximum links" => ($linkType->range_max == 0 ? "Unrestricted" : $linkType->range_max)
            ],
            "Label" => $this->titleMaker->title($linkType),
            "Inverse Label" => $this->titleMaker->title($linkType, 'inverse'),
        ];
        return view('linkType.show', [
            "linkType" => $linkType,
            "data" => $data,
            "nav" => $this->navigationMaker->documentRevisionNavigation($linkType->documentRevision)]);
    }


    /**
     * Display the links of this type
     *
     * @param LinkType $linkType
     * @return Response
     */
    public function links(LinkType $linkType)
    {
        return view('linkType.links', [
            "linkType" => $linkType,
            "nav" => $this->navigationMaker->documentRevisionNavigation($linkType->documentRevision)]);
    }

    /**
     * @param Request $request
     * @param LinkType $linkType
     * @return Response
     */
    public function createLink(Request $request, LinkType $linkType)
    {
        $link = new Link();
        $link->documentRevision()->associate($linkType->documentRevision);
        $link->link_type_sid = $linkType->sid;
        $data = $this->requestProcessor->fromLinkRequest();
        $mmReturn = $this->requestProcessor->returnURL();

        if (isset($data["subject"])) {
            $link->subject_sid = $data["subject"];
        }
        if (isset($data["object"])) {
            $link->object_sid = $data["object"];
        }
        return view('link.create', [
            "link" => $link,
            "idPrefix" => "",
            "returnTo" => $mmReturn,
            "nav" => $this->navigationMaker->documentRevisionNavigation($linkType->documentRevision)
        ]);
    }

    /**
     * @param Request $request
     * @param LinkType $linkType
     * @return RedirectResponse
     * @throws Exception
     */
    public function storeLink(Request $request, LinkType $linkType)
    {
        $action = $request->get("_mmaction", "");
        $returnLink = $request->get("_mmreturn",
            $this->linkMaker->url($linkType->documentRevision));
        if ($action == "cancel") {
            return Redirect::to($returnLink);
        }
        if ($action != "save") {
            throw new Exception("Unknown action '$action'");
        }

        $link = new Link();
        $link->documentRevision()->associate($linkType->documentRevision);
        $link->link_type_sid = $linkType->sid;
        $data = $this->requestProcessor->fromLinkRequest();
        if (isset($data["subject"])) {
            $link->subject_sid = $data["subject"];
        }
        if (isset($data["object"])) {
            $link->object_sid = $data["object"];
        }

        try {
            $link->validate();
        } catch (MMValidationException $exception) {
            return Redirect::to($this->linkMaker->url($linkType, 'create-link'))
                ->withInput()
                ->withErrors($exception->getMessage());
        }
        $link->save();
        return Redirect::to($returnLink)
            ->with("message", "Link created.");
    }

}
