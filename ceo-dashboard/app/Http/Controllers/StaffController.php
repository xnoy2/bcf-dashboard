<?php

namespace App\Http\Controllers;

use App\Services\Portals\StaffService;

class StaffController extends Controller
{
    public function __construct(private StaffService $staff)
    {
    }

    public function index()
    {
        return view('staff', [
            'accounts' => config('integrations.accounts'),
            'account'  => 'all',
            'staff'    => $this->staff->overview(),
        ]);
    }
}
