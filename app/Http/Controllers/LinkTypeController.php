<?php

namespace App\Http\Controllers;

use App\Models\LinkType;
use Response;

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
                "Minimum links" => $linkType->domain_min,
                "Maximum links" => ($linkType->domain_max == 0 ? "Unrestricted" : $linkType->domain_max)
            ],
            "Links to" => [
                "Record type" => $this->titleMaker->title($linkType->range),
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


}
