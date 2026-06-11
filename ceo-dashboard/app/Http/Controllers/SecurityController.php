<?php

namespace App\Http\Controllers;

use App\Services\Cloudflare\SecurityService;

class SecurityController extends Controller
{
    public function __construct(private SecurityService $security)
    {
    }

    public function index()
    {
        return view('security', [
            'accounts' => config('integrations.accounts'),
            'account'  => 'all',
            'security' => $this->security->overview(),
        ]);
    }
}
