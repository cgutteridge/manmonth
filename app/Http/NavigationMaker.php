<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:42
 */

namespace App\Http;


use App;
use App\Models\Document;
use App\Models\DocumentRevision;
use App\Models\LinkType;
use Auth;
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
            if (!$recordType->isProtected()) {
                $createItems [] = [
                    "glyph" => "plus-sign",
                    "label" => $this->titleMaker->title($recordType),
                    "href" => $this->linkMaker->url($recordType, "create-record"),
                    "allowed" => Auth::user()->can('edit-data', $documentRevision->document)
                ];
                $browseItems [] = [
                    "glyph" => "list",
                    "label" => $this->titleMaker->title($recordType),
                    "href" => $this->linkMaker->url($recordType, "records"),
                    "allowed" => Auth::user()->can('view', $documentRevision)
                ];
            }
            $schemaItems [] = [
                "glyph" => "cog",
                "label" => $this->titleMaker->title($recordType),
                "href" => $this->linkMaker->url($recordType),
                "allowed" => Auth::user()->can('view', $documentRevision)
            ];
        }
        /** @var LinkType $linkType */
        foreach ($documentRevision->linkTypes as $linkType) {
            $browseItems [] = ["glyph" => "list",
                "label" => "LINK: " . $this->titleMaker->title($linkType->domain()) . "&rarr;" . $this->titleMaker->title($linkType) . "&rarr;" . $this->titleMaker->title($linkType->range()),
                "href" => $this->linkMaker->url($linkType, "links"),
                "allowed" => Auth::user()->can('view', $documentRevision)];
            $schemaItems [] = ["glyph" => "cog",
                "label" => "LINK: " . $this->titleMaker->title($linkType),
                "href" => $this->linkMaker->url($linkType),
                "allowed" => Auth::user()->can('view', $documentRevision)];
        }

        $ritems = [];
        $ritems [] = [
            "glyph" => "file",
            "label" => "View Revision",
            "href" => $this->linkMaker->url($documentRevision),
            "allowed" => Auth::user()->can('view', $documentRevision)
        ];
        $ritems [] = [
            "glyph" => "list",
            "label" => "Browse",
            "items" => $browseItems,
            "allowed" => Auth::user()->can('view', $documentRevision)
        ];
        $ritems [] = [
            "glyph" => "plus-sign",
            "label" => "Create",
            "items" => $createItems,
            "allowed" => Auth::user()->can('create', $documentRevision)
        ];
        if ($documentRevision->status == 'draft') {
            $ritems [] = [
                "glyph" => "circle-arrow-up",
                "label" => "Commit draft and continue editing",
                "href" => $this->linkMaker->url($documentRevision, "commit-and-continue"),
                "allowed" => Auth::user()->can('commit', $documentRevision->document)
            ];
            $ritems [] = [
                "glyph" => "circle-arrow-up",
                "label" => "Commit draft",
                "href" => $this->linkMaker->url($documentRevision, "commit"),
                "allowed" => Auth::user()->can('commit', $documentRevision->document)
            ];
            if (Auth::user()->can('publish', $documentRevision->document)) {
                // inside an if() as this requires commit AND publish
                $ritems [] = [
                    "glyph" => "circle-arrow-up",
                    "label" => "Commit draft and publish it",
                    "href" => $this->linkMaker->url($documentRevision, "commit-and-publish"),
                    "allowed" => Auth::user()->can('commit', $documentRevision->document)
                ];
            }
            $ritems [] = [
                "label" => "Scrap draft",
                "glyph" => "circle-arrow-down",
                "href" => $this->linkMaker->url($documentRevision, "scrap"),
                "allowed" => Auth::user()->can('commit', $documentRevision->document)
            ];

            $ritems [] = [
                "label" => "Configuration",
                "glyph" => "cog",
                "href" => $this->linkMaker->url($documentRevision->configRecord(), "edit"),
                "allowed" => Auth::user()->can('edit', $documentRevision->configRecord())
            ];
        }
        if ($documentRevision->status != 'draft') {
            $ritems [] = [
                "label" => "Configuration",
                "glyph" => "cog",
                "href" => $this->linkMaker->url($documentRevision->configRecord()),
                "allowed" => Auth::user()->can('view', $documentRevision->configRecord())
            ];
        }
        $ritems [] = [
            "glyph" => "cog",
            "label" => "Schema",
            "items" => $schemaItems,
            "allowed" => Auth::user()->can('view', $documentRevision)
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
            case "scrap":
                $nav["side"]["status"] = "scrap";
                $nav["side"]["label"] = "This is a scrapped revision";
                break;
            case "draft":
                $nav["side"]["status"] = "draft";
                $nav["side"]["label"] = "This is the draft revision";
                break;
            case "archive":
                $nav["side"]["status"] = "archive";
                $nav["side"]["label"] = "This is a committed revision";
                if ($documentRevision->published) {
                    $latestPublic = $documentRevision->document->latestPublishedRevision();
                    $nav["side"]["label"] = "This is a published revision";

                    if ($latestPublic != null && $documentRevision->id == $latestPublic->id) {
                        $nav["side"]["status"] = "current";
                        $nav["side"]["label"] = "This is the latest published revision";
                    }
                }
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
    public
    function documentNavigation(Document $document)
    {
        $nav = $this->defaultNavigation();
        /*
        $nav["title"] = [
            "label" => $document->name,
            "href" => $this->linkMaker->url($document)
        ];
        */
        $docItems = [];
        $docItems [] =
            [
                "glyph" => "file",
                "label" => "Latest published revision",
                "href" => $this->linkMaker->url($document, "latest-published"),
                "allowed" => Auth::user()->can('view-published-latest', $document)
            ];
        $docItems [] =

            [
                "glyph" => "file",
                "label" => "Latest revision",
                "href" => $this->linkMaker->url($document, "latest"),
                "allowed" => Auth::user()->can('view-archive', $document)
            ];
        $draft = $document->draftRevision();
        if ($draft) {
            $docItems [] =
                [
                    "glyph" => "file",
                    "label" => "Draft revision",
                    "href" => $this->linkMaker->url($document, "draft"),
                    "allowed" => Auth::user()->can('view-draft', $document)
                ];
        } else {
            $docItems [] =
                [
                    "glyph" => "file",
                    "label" => "Create draft from latest revision",
                    "href" => $this->linkMaker->url($document, "create-draft"),
                    "allowed" => Auth::user()->can('commit', $document)
                ];
        }
        $docItems [] =
            [
                "glyph" => "list",
                "label" => "All revisions",
                "href" => $this->linkMaker->url($document),
                "allowed" => Auth::user()->can('view-archive', $document)
            ];

        $nav["menus"] = [
            [
                "label" => $document->name,
                "glyph" => "file",
                "items" => $docItems
            ]
        ];
        return $nav;
    }

    /**
     * @return array
     */
    public
    function defaultNavigation()
    {
        $nav = [];
        $nav["title"] = ["label" => ""];
        if (App::environment('prod')) {
            ; // do nothing
        } elseif (App::environment('pprd')) {
            $nav["sitestatus"] = "Pre-production instance";
        } else {
            $nav["sitestatus"] = "Development instance";
        }
        if (Auth::check()) {
            $nav["usermenu"] = [
                "label" => Auth::user()->name,
                "glyph" => "user",
                "items" => [
                    [
                        "label" => "Profile",
                        "href" => "/profile",
                        "glyph" => "user"
                    ],
                    [
                        "label" => "Logout",
                        "href" => "/logout",
                        "glyph" => "log-out"
                    ]
                ]
            ];
        }

        return $nav;
    }

}