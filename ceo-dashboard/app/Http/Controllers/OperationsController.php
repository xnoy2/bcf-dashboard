<?php

namespace App\Http\Controllers;

use App\Services\Portals\OperationsService;

class OperationsController extends Controller
{
    public function __construct(private OperationsService $operations)
    {
    }

    public function index()
    {
        return view('operations', [
            'accounts' => config('integrations.accounts'),
            'account'  => 'all',
            'ops'      => $this->operations->overview(),
        ]);
    }
}
