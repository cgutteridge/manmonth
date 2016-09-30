<?php

namespace App\Http\Controllers;

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

    /**
     * DocumentController constructor.
     * @param NavigationMaker $navigationMaker
     * @param LinkMaker $linkMaker
     */
    public function __construct(NavigationMaker $navigationMaker, LinkMaker $linkMaker)
    {
        $this->navigationMaker = $navigationMaker;
        $this->linkMaker = $linkMaker;
    }
}
