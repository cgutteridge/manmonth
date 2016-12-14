<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Response;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('permission.index', [
            "globalPermissions" => Permission::globalPermissions(),
            "documentPermissions" => Permission::documentPermissions(),
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

}
