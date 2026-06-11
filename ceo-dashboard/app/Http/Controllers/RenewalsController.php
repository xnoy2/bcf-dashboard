<?php

namespace App\Http\Controllers;

use App\Services\GoDaddy\GoDaddyService;

class RenewalsController extends Controller
{
    public function __construct(private GoDaddyService $godaddy)
    {
    }

    public function index()
    {
        return view('renewals', [
            'accounts'  => config('integrations.accounts'),
            'account'   => 'all',
            'renewals'  => $this->godaddy->overview(),
        ]);
    }
}
