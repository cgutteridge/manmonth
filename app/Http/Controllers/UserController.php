<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Redirect;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $list = User::all();

        return view('user.index', [
            "list" => $list,
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        return view('featureNotDoneYet', [
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Display the specified resource.
     * @param User $user
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(User $user)
    {
        $this->authorize('full-user-admin');

        return view('user.show', [
            'user' => $user,
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }

    /**
     * Display the specified resource.
     * @return RedirectResponse
     * @throws \App\Exceptions\MMValidationException
     */
    public function profile()
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return Redirect::to("/")
                ->withErrors("Can't show profile; No current user.");
        }

        $rolesInfo = [];
        foreach ($user->allRoles() as $role) {
            $document = $role->document;
            $id = "general";
            if ($document) {
                $id = $document->id;
            }
            if (!isset($rolesInfo[$id])) {
                $rolessInfo[$id] = [
                    "permissions" => [],
                    "roles" => []
                ];

                if ($document) {
                    $rolesInfo[$id]["document"] = [
                        "url" => $this->linkMaker->url($document),
                        "title" => $this->titleMaker->title($document),
                    ];
                }
            }
            $rolesInfo[$id]["roles"][] = [
                "title" => $role->label,
            ];
            foreach ($role->permissions as $permission) {
                $rolesInfo[$id]["permissions"][$permission->name] = [
                    "title" => $permission->label,
                    "name" => $permission->name];
            }
        }
        return view('user.show', [
            'user' => $user,
            'roles' => $rolesInfo,
            'nav' => $this->navigationMaker->defaultNavigation()
        ]);
    }
}
