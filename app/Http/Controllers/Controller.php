<?php

namespace App\Http\Controllers;

use App\Http\LinkMaker;
use App\Http\NavigationMaker;
use App\Http\RequestProcessor;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    protected $linkMaker;
    protected $navigationMaker;
    protected $requestProcessor;

    /**
     * DocumentController constructor.
     * @param NavigationMaker $navigationMaker
     * @param LinkMaker $linkMaker
     * @param RequestProcessor $requestProcessor
     */
    public function __construct(NavigationMaker $navigationMaker, LinkMaker $linkMaker, RequestProcessor $requestProcessor)
    {
        $this->navigationMaker = $navigationMaker;
        $this->linkMaker = $linkMaker;
        $this->requestProcessor = $requestProcessor;
    }
}
