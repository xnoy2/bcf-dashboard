<?php

namespace App\Http\Controllers;

use App\Services\Portals\ClientProjectsService;

class ClientProjectsController extends Controller
{
    public function __construct(private ClientProjectsService $projects)
    {
    }

    public function index()
    {
        return view('client_projects', [
            'accounts' => config('integrations.accounts'),
            'account'  => 'all',
            'data'     => $this->projects->overview(),
        ]);
    }
}
