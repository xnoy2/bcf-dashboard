<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Read-only integration API (v1)
|--------------------------------------------------------------------------
| Lets external apps pull the same cached dashboard data over JSON. Every
| route is guarded by the static DASHBOARD_API_KEY ("api.key" middleware)
| and rate-limited. Responses are pure DB reads of the snapshot cache, so
| they are fast and never trigger live upstream API calls on the request.
*/

Route::prefix('v1')
    ->middleware(['api.key', 'throttle:120,1'])
    ->group(function () {
        // Health + discovery.
        Route::get('/ping', [ApiController::class, 'ping']);

        // Account-aware (?account=all|bcf|bgr|rg, default all).
        Route::get('/overview', [ApiController::class, 'overview']);
        Route::get('/pipeline', [ApiController::class, 'pipeline']);
        Route::get('/finance', [ApiController::class, 'finance']);
        Route::get('/appointments', [ApiController::class, 'appointments']);

        // Global (single source, no account filter).
        Route::get('/security', [ApiController::class, 'security']);
        Route::get('/staff', [ApiController::class, 'staff']);
        Route::get('/client-projects', [ApiController::class, 'clientProjects']);
        Route::get('/operations', [ApiController::class, 'operations']);
        Route::get('/work-report', [ApiController::class, 'workReport']);
        Route::get('/renewals', [ApiController::class, 'renewals']);
    });
