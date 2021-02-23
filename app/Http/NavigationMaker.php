<?php /** @noinspection ALL */

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
use App\Models\Record;
use App\Models\RecordType;
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
     * @param Record $record
     * @param null|string $pageName
     * @return array
     * @throws App\Exceptions\MMValidationException
     */
    public function recordNavigation(Record $record, $pageName = null)
    {
        $nav = $this->recordTypeNavigation($record->recordType);
        $nav["breadcrumbs"][] = [
            "label" => $this->titleMaker->title($record),
            "href" => $this->linkMaker->url($record),
        ];
        if (!empty($pageName)) {
            $nav["breadcrumbs"][] = ["label" => $pageName];
        }
        return $nav;
    }

    /**
     * @param RecordType $recordType
     * @param null|string $pageName
     * @return array
     * @throws App\Exceptions\MMValidationException
     */
    public function recordTypeNavigation(RecordType $recordType, $pageName = null)
    {
        $nav = $this->documentRevisionNavigation($recordType->documentRevision);
        $nav["breadcrumbs"][] = [
            "label" => $this->titleMaker->title($recordType),
            "href" => $this->linkMaker->url($recordType, "records"),
        ];
        if (!empty($pageName)) {
            $nav["breadcrumbs"][] = ["label" => $pageName];
        }
        return $nav;
    }

    /**
     * @param DocumentRevision $documentRevision
     * @param null|string $pageName
     * @return array
     * @throws Exception
     */
    public function documentRevisionNavigation(DocumentRevision $documentRevision, $pageName = null)
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
            "label" => "View revision",
            "href" => $this->linkMaker->url($documentRevision),
            "allowed" => Auth::user()->can('view', $documentRevision)
        ];

        if ($documentRevision->status == 'draft') {
            $ritems [] = [
                "glyph" => "circle-arrow-up",
                "label" => "Commit and start new revision",
                "href" => $this->linkMaker->url($documentRevision, "commit-and-continue"),
                "allowed" => Auth::user()->can('commit-revision', $documentRevision)
            ];
            if (Auth::user()->can('publish', $documentRevision->document)) {
                // inside an if() as this requires commit AND publish
                $ritems [] = [
                    "glyph" => "circle-arrow-up",
                    "label" => "Commit and publish revision",
                    "href" => $this->linkMaker->url($documentRevision, "commit-and-publish"),
                    "allowed" => Auth::user()->can('commit-revision', $documentRevision)
                ];
            }
            $ritems [] = [
                "glyph" => "circle-arrow-up",
                "label" => "Commit revision",
                "href" => $this->linkMaker->url($documentRevision, "commit"),
                "allowed" => Auth::user()->can('commit-revision', $documentRevision)
            ];

            $ritems [] = [
                "label" => "Scrap revision",
                "glyph" => "circle-arrow-down",
                "href" => $this->linkMaker->url($documentRevision, "scrap"),
                "allowed" => Auth::user()->can('commit-revision', $documentRevision)
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

        $nav["menus"] [] = [
            "label" => "Data",
            "items" => $browseItems,
            "allowed" => Auth::user()->can('view', $documentRevision)
        ];
        $nav["menus"] [] = [
            "label" => "New",
            "items" => $createItems,
            "allowed" => Auth::user()->can('create', $documentRevision)
        ];

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
        $crumb = "???";
        switch ($documentRevision->status) {
            case "scrap":
                $nav["side"]["status"] = "scrap";
                $nav["side"]["label"] = "This is a scrapped revision";
                $crumb = "Scrapped";
                break;
            case "draft":
                $nav["side"]["status"] = "draft";
                $nav["side"]["label"] = "This is the draft revision";
                $crumb = "Draft";
                break;
            case "archive":
                $nav["side"]["status"] = "archive";
                $nav["side"]["label"] = "This is a committed revision";
                $crumb = "Committed";

                if ($documentRevision->published) {
                    $latestPublic = $documentRevision->document->latestPublishedRevision();
                    $nav["side"]["label"] = "This is a published revision";
                    $crumb = "Public";

                    if ($latestPublic != null && $documentRevision->id == $latestPublic->id) {
                        $nav["side"]["status"] = "current";
                        $nav["side"]["label"] = "This is the latest published revision";
                        $crumb = "Latest public";
                    }
                }
                break;
            default:
                throw new Exception("Unknown document status: " . $documentRevision->status);
        }
        if ($crumb == "Draft" || $crumb == "Latest public") {
            $nav["breadcrumbs"][] = [
                "label" => $crumb,
                "href" => $this->linkMaker->url($documentRevision)
            ];
        } else {
            $nav["breadcrumbs"][] = ["label" => $crumb];
            $nav["breadcrumbs"][] = [
                "label" => "Revision #" . $documentRevision->id,
                "href" => $this->linkMaker->url($documentRevision)
            ];
        }
        if (!empty($pageName)) {
            $nav["breadcrumbs"][] = ["label" => $pageName];
        }

        return $nav;
    }

    /**
     * @param Document $document
     * @param null|string $pageName
     * @return array
     * @throws Exception
     */
    public
    function documentNavigation(Document $document, $pageName = null)
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
                "label" => "Latest committed revision",
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
                    "label" => "Start new revision",
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
        $docItems [] =
            [
                "glyph" => "list",
                "label" => "Clone document",
                "href" => $this->linkMaker->url($document, "create-clone"),
                "allowed" => Auth::user()->can('full-document-admin', $document)
            ];

        $nav["menus"] = [
            [
                "label" => $document->name,
                "glyph" => "file",
                "items" => $docItems
            ]
        ];
        $nav["breadcrumbs"] = [
            [
                "label" => $document->name,
                "href" => $this->linkMaker->url($document)
            ]
        ];
        if (!empty($pageName)) {
            $nav["breadcrums"][] = ["label" => $pageName];
        }

        return $nav;
    }

    /**
     * @param null|string $pageName
     * @return array
     */
    public
    function defaultNavigation($pageName = null)
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

        $nav["breadcrumbs"] = [];
        if (!empty($pageName)) {
            $nav["breadcrumbs"][] = ["label" => $pageName];
        }

        return $nav;
    }

}