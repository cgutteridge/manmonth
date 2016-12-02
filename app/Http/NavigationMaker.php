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
use App\Models\LinkType;
use Exception;

/**
 * @property LinkMaker linkMaker
 * @property TitleMaker titleMaker
 */
class NavigationMaker
{
    function __construct(LinkMaker $linkMaker, TitleMaker $titleMaker)
    {
        $this->linkMaker = $linkMaker;
        $this->titleMaker = $titleMaker;
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
     * @param DocumentRevision $documentRevision
     * @return array
     * @throws Exception
     */
    public function documentRevisionNavigation(DocumentRevision $documentRevision)
    {
        $nav = $this->documentNavigation($documentRevision->document);

        $createItems = [];
        $browseItems = [];
        $schemaItems = [];
        foreach ($documentRevision->recordTypes as $recordType) {
            $createItems [] = [
                "glyph" => "plus-sign",
                "label" => $this->titleMaker->title($recordType),
                "href" => $this->linkMaker->url($recordType, "create-record")
            ];
            $browseItems [] = [
                "glyph" => "list",
                "label" => $this->titleMaker->title($recordType),
                "href" => $this->linkMaker->url($recordType, "records")
            ];
            $schemaItems [] = [
                "glyph" => "cog",
                "label" => $this->titleMaker->title($recordType),
                "href" => $this->linkMaker->url($recordType)
            ];
        }
        /** @var LinkType $linkType */
        foreach ($documentRevision->linkTypes as $linkType) {
            $browseItems [] = [
                "glyph" => "list",
                "label" => "LINK: " . $this->titleMaker->title($linkType->domain) . "&rarr;" . $this->titleMaker->title($linkType) . "&rarr;" . $this->titleMaker->title($linkType->range),
                "href" => $this->linkMaker->url($linkType, "links")
            ];
            $schemaItems [] = [
                "glyph" => "cog",
                "label" => $this->titleMaker->title($linkType),
                "href" => $this->linkMaker->url($linkType)
            ];
        }

        $ritems = [];
        $ritems [] = [
            "glyph" => "file",
            "label" => "View Revision",
            "href" => $this->linkMaker->url($documentRevision)
        ];
        $ritems [] = [
            "glyph" => "list",
            "label" => "Browse",
            "items" => $browseItems];
        $ritems [] = [
            "glyph" => "plus-sign",
            "label" => "Create",
            "items" => $createItems];
        $ritems [] = [
            "glyph" => "circle-arrow-up",
            "label" => "Publish",
            "href" => $this->linkMaker->url($documentRevision, "publish")
        ];
        $ritems [] = [
            "label" => "Scrap",
            "glyph" => "circle-arrow-down",
            "href" => $this->linkMaker->url($documentRevision, "scrap")
        ];
        $ritems [] = [
            "glyph" => "cog",
            "label" => "Schema",
            "items" => $schemaItems
        ];
        $nav["menus"][] = [
            "label" => "Revision",
            "items" => $ritems];

        $reportItems = [];
        foreach ($documentRevision->reportTypes as $reportType) {
            $reportItems [] = [
                "href" => $this->linkMaker->url($reportType),
                "label" => $this->titleMaker->title($reportType)
            ];
        }
        $nav["menus"][] = [
            "label" => "Reports",
            "items" => $reportItems];



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

    /**
     * @param Document $document
     * @return array
     */
    public function documentNavigation(Document $document)
    {
        return [
            "title" => [
                "label" => $document->name,
                "href" => $this->linkMaker->url($document)
            ],
            "menus" => [
                [
                    "label" => "Document",
                    "items" => [
                        [
                            "glyph" => "file",
                            "label" => "Current",
                            "href" => $this->linkMaker->url($document, "current")
                        ],
                        [
                            "glyph" => "file",
                            "label" => "Draft",
                            "href" => $this->linkMaker->url($document, "draft")
                        ],
                        [
                            "glyph" => "list",
                            "label" => "All revisions",
                            "href" => $this->linkMaker->url($document)
                        ]
                    ]
                ]
            ]
        ];
    }

    // TODO scrap

    // TODO publish
}