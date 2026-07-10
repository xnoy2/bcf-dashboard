<?php

namespace App\Providers;

use App\Models\GhlAccount;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->mergeDbGhlAccounts();
    }

    /**
     * Merge DB-managed GoHighLevel sub-accounts into the static accounts config,
     * so the switcher and every account-aware service pick them up transparently.
     */
    private function mergeDbGhlAccounts(): void
    {
        try {
            if (! Schema::hasTable('ghl_accounts')) {
                return;
            }
            $extra = GhlAccount::asConfig();
            if ($extra) {
                config([
                    'integrations.accounts' => array_merge(config('integrations.accounts', []), $extra),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
