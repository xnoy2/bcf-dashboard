<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve the selected business account for account-aware pages
     * (CEO Overview + the Growth pages), remembering the choice in the
     * session so it survives navigation between those pages.
     */
    protected function resolveAccount(Request $request): string
    {
        $accounts = config('integrations.accounts', []);
        $session  = $request->hasSession() ? $request->session() : null;

        $account = $request->query('account');

        // No explicit choice in the URL → fall back to the remembered one.
        if ($account === null && $session) {
            $account = $session->get('selected_account');
        }

        if ($account !== 'all' && ! isset($accounts[$account])) {
            $account = 'all';
        }

        $session?->put('selected_account', $account);

        return $account;
    }
}
