<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:42
 */

namespace App\Http;


use App\Models\Document;
use App\Models\DocumentRevision;
use Exception;

class NavigationMaker
{
    function __construct(LinkMaker $linkMaker)
    {
        $this->linkMaker = $linkMaker;
    }

    /**
     * @return array
     */
    public function defaultNavigation()
    {
        return [
            "title" => ["label" => "Man Month"]
        ];
    }

    /**
     * @param Document $document
     * @return array
     */
    public function documentNavigation(Document $document)
    {
        return [
            "title" => [
                "label" => $document->name,
                "href" => $this->linkMaker->link($document)
            ],
            "menus" => [
                [
                    "label" => "Document",
                    "items" => [
                        [
                            "label" => "Current",
                            "href" => $this->linkMaker->link($document) . "/current"
                        ],
                        [
                            "label" => "Draft",
                            "href" => $this->linkMaker->link($document) . "/draft"
                        ],
                        [
                            "label" => "All revisions",
                            "href" => $this->linkMaker->link($document)
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param DocumentRevision $documentRevision
     * @return array
     * @throws Exception
     */
    public function documentRevisionNavigation(DocumentRevision $documentRevision)
    {
        $nav = $this->documentNavigation($documentRevision->document);

        $createItems = [];
        $browseItems = [];
        $schemaRecordItems = [];
        $schemaLinkItems = [];
        foreach ($documentRevision->recordTypes as $recordType) {
            $createItems [] = [
                "label" => $recordType->title(),
                "href" => $this->linkMaker->link($recordType) . "/create-record"
            ];
            $browseItems [] = [
                "label" => $recordType->title(),
                "href" => $this->linkMaker->link($recordType) . "/records"
            ];
            $schemaRecordItems [] = [
                "label" => $recordType->title(),
                "href" => $this->linkMaker->link($recordType)
            ];
        }
        foreach ($documentRevision->linkTypes as $linkType) {
            $schemaLinkItems [] = [
                "label" => $linkType->title(),
                "href" => $this->linkMaker->link($linkType)
            ];
        }

        $ritems = [];
        $ritems [] = [
            "label" => "View Revision",
            "href" => $this->linkMaker->link($documentRevision)
        ];
        $ritems [] = [
            "label" => "Browse",
            "items" => $browseItems];
        $ritems [] = [
            "label" => "Create",
            "items" => $createItems];
        $ritems [] = [
            "label" => "Publish",
            "href" => $this->linkMaker->link($documentRevision) . "/publish"
        ];
        $ritems [] = [
            "label" => "Scrap",
            "href" => $this->linkMaker->link($documentRevision) . "/scrap"
        ];
        $ritems [] = [
            "label" => "Schema",
            "items" => [
                [
                    "label" => "Record types",
                    "items" => $schemaRecordItems
                ],
                [
                    "label" => "Link types",
                    "items" => $schemaLinkItems
                ]
            ]
        ];
        $nav["menus"][] = [
            "label" => "Revision",
            "items" => $ritems];


        $nav["menus"][] = [
            "label" => "Reports",
            "items" => []];

        $nav["side"] = [];
        switch ($documentRevision->status) {
            case "current":
                $nav["side"]["status"] = "current";
                $nav["side"]["label"] = "This is the current revision";
                break;
            case "scrap":
                $nav["side"]["status"] = "scrap";
                $nav["side"]["label"] = "This is a scrapped draft revision";
                break;
            case "draft":
                $nav["side"]["status"] = "draft";
                $nav["side"]["label"] = "This is the draft revision";
                break;
            case "archive":
                $nav["side"]["status"] = "archive";
                $nav["side"]["label"] = "This is an archived revision";
                break;
            default:
                throw new Exception("Unknown document status: " . $documentRevision->status);
        }

        return $nav;
    }

    // TODO scrap

    // TODO publish
}