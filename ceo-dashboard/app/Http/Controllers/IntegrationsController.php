<?php

namespace App\Http\Controllers;

use App\Models\GhlLocation;
use App\Models\GhlOauthToken;
use App\Services\Ghl\GhlOAuthService;
use Illuminate\Http\Request;

class IntegrationsController extends Controller
{
    public function __construct(private GhlOAuthService $ghl)
    {
    }

    public function index()
    {
        return view('integrations', [
            'configured' => $this->ghl->isConfigured(),
            'connection' => GhlOauthToken::current(),
            'locations'  => GhlLocation::orderBy('name')->get(),
        ]);
    }

    /** Send the agency admin to GoHighLevel to approve the app. */
    public function connect()
    {
        abort_unless($this->ghl->isConfigured(), 503, 'GoHighLevel OAuth is not configured.');

        return redirect()->away($this->ghl->authorizeUrl());
    }

    /** OAuth callback — exchange the code and discover sub-accounts. */
    public function callback(Request $request)
    {
        if ($request->query('error')) {
            return redirect()->route('integrations.index')
                ->with('error', 'GoHighLevel authorisation was cancelled or failed: ' . $request->query('error'));
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()->route('integrations.index')->with('error', 'No authorisation code returned by GoHighLevel.');
        }

        try {
            $this->ghl->exchangeCode($code);
            $count = $this->ghl->discoverLocations();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('integrations.index')
                ->with('error', 'Could not complete the GoHighLevel connection: ' . $e->getMessage());
        }

        return redirect()->route('integrations.index')
            ->with('refreshed', "GoHighLevel connected. Discovered {$count} sub-account(s).");
    }

    /** Re-sync the list of sub-accounts. */
    public function sync()
    {
        try {
            $count = $this->ghl->discoverLocations();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }

        return back()->with('refreshed', "Synced {$count} sub-account(s) from GoHighLevel.");
    }

    public function disconnect()
    {
        $this->ghl->disconnect();

        return redirect()->route('integrations.index')->with('refreshed', 'GoHighLevel disconnected.');
    }
}
