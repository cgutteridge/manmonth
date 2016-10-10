<?php

namespace App\Http\Controllers;

use App\Http\LinkMaker;
use App\Http\NavigationMaker;
use App\Http\RequestProcessor;
use App\Http\TitleMaker;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    protected $linkMaker;
    protected $navigationMaker;
    protected $requestProcessor;
    protected $titleMaker;

    /**
     * DocumentController constructor.
     * @param NavigationMaker $navigationMaker
     * @param LinkMaker $linkMaker
     * @param TitleMaker $titleMaker
     * @param RequestProcessor $requestProcessor
     */
    public function __construct(NavigationMaker $navigationMaker, LinkMaker $linkMaker, TitleMaker $titleMaker, RequestProcessor $requestProcessor)
    {
        $this->navigationMaker = $navigationMaker;
        $this->linkMaker = $linkMaker;
        $this->titleMaker = $titleMaker;
        $this->requestProcessor = $requestProcessor;
    }
}
