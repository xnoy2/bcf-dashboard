<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Boards\BoardController;
use App\Http\Controllers\Boards\BoardListController;
use App\Http\Controllers\Boards\CardAttachmentController;
use App\Http\Controllers\Boards\CardCommentController;
use App\Http\Controllers\Boards\CardController;
use App\Http\Controllers\Boards\ChecklistController;
use App\Http\Controllers\Boards\ChecklistItemController;
use App\Http\Controllers\Boards\LabelController;
use App\Http\Controllers\Boards\WorkspaceController;
use App\Http\Controllers\Boards\WorkspaceMemberController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientProjectsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\RefreshController;
use App\Http\Controllers\RenewalsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// --- Guest ---
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// --- Authenticated ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sales', [SalesController::class, 'index'])->name('sales');
    Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
    Route::get('/operations', [OperationsController::class, 'index'])->name('operations');
    Route::get('/client-projects', [ClientProjectsController::class, 'index'])->name('client-projects');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/security', [SecurityController::class, 'index'])->name('security');
    Route::get('/renewals', [RenewalsController::class, 'index'])->name('renewals');
    Route::post('/refresh-data', RefreshController::class)->name('refresh-data');

    // --- Integrations (GoHighLevel agency OAuth) ---
    Route::get('/integrations', [IntegrationsController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/crm/connect', [IntegrationsController::class, 'connect'])->name('integrations.connect');
    Route::get('/integrations/crm/callback', [IntegrationsController::class, 'callback'])->name('integrations.callback');
    Route::post('/integrations/crm/sync', [IntegrationsController::class, 'sync'])->name('integrations.sync');
    Route::post('/integrations/crm/disconnect', [IntegrationsController::class, 'disconnect'])->name('integrations.disconnect');

    // --- Boards (Trello-style workspaces / boards / lists / cards) ---
    Route::get('/boards', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::patch('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');
    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy'])->name('workspaces.destroy');

    Route::get('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'index'])->name('members.index');
    Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store'])->name('members.store');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'destroy'])->name('members.destroy');

    Route::post('/workspaces/{workspace}/boards', [BoardController::class, 'store'])->name('boards.store');
    Route::get('/boards/{board}', [BoardController::class, 'show'])->name('boards.show');
    Route::patch('/boards/{board}', [BoardController::class, 'update'])->name('boards.update');
    Route::delete('/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');

    Route::post('/boards/{board}/lists', [BoardListController::class, 'store'])->name('lists.store');
    Route::post('/boards/{board}/lists/reorder', [BoardListController::class, 'reorder'])->name('lists.reorder');
    Route::patch('/lists/{list}', [BoardListController::class, 'update'])->name('lists.update');
    Route::post('/lists/{list}/duplicate', [BoardListController::class, 'duplicate'])->name('lists.duplicate');
    Route::delete('/lists/{list}', [BoardListController::class, 'destroy'])->name('lists.destroy');

    Route::post('/lists/{list}/cards', [CardController::class, 'store'])->name('cards.store');
    Route::post('/lists/{list}/cards/reorder', [CardController::class, 'reorder'])->name('cards.reorder');
    Route::get('/cards/{card}', [CardController::class, 'show'])->name('cards.show');
    Route::patch('/cards/{card}', [CardController::class, 'update'])->name('cards.update');
    Route::delete('/cards/{card}', [CardController::class, 'destroy'])->name('cards.destroy');
    Route::post('/cards/{card}/labels', [CardController::class, 'toggleLabel'])->name('cards.labels.toggle');
    Route::post('/cards/{card}/members', [CardController::class, 'toggleMember'])->name('cards.members.toggle');

    Route::post('/boards/{board}/labels', [LabelController::class, 'store'])->name('labels.store');
    Route::patch('/labels/{label}', [LabelController::class, 'update'])->name('labels.update');
    Route::delete('/labels/{label}', [LabelController::class, 'destroy'])->name('labels.destroy');

    Route::post('/cards/{card}/checklists', [ChecklistController::class, 'store'])->name('checklists.store');
    Route::delete('/checklists/{checklist}', [ChecklistController::class, 'destroy'])->name('checklists.destroy');
    Route::post('/checklists/{checklist}/items', [ChecklistItemController::class, 'store'])->name('items.store');
    Route::patch('/checklist-items/{item}', [ChecklistItemController::class, 'update'])->name('items.update');
    Route::delete('/checklist-items/{item}', [ChecklistItemController::class, 'destroy'])->name('items.destroy');

    Route::post('/cards/{card}/comments', [CardCommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CardCommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/cards/{card}/attachments', [CardAttachmentController::class, 'store'])->name('attachments.store');
    Route::post('/cards/{card}/attachments/presign', [CardAttachmentController::class, 'presign'])->name('attachments.presign');
    Route::post('/cards/{card}/attachments/record', [CardAttachmentController::class, 'record'])->name('attachments.record');
    Route::get('/card-attachments/{attachment}', [CardAttachmentController::class, 'show'])->name('boards.attachments.show');
    Route::delete('/card-attachments/{attachment}', [CardAttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Calendar module
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::post('/calendar/{entry}', [CalendarController::class, 'update'])->name('calendar.update'); // POST + _method or files
    Route::delete('/calendar/{entry}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::get('/calendar/attachment/{attachment}', [CalendarController::class, 'attachment'])->name('calendar.attachment');
    Route::delete('/calendar/attachment/{attachment}', [CalendarController::class, 'destroyAttachment'])->name('calendar.attachment.destroy');
});

// Local-only: auto-login preview used for layout QA screenshots. 404s outside local.
if (app()->environment('local')) {
    Route::get('/__preview/{path}', function (string $path) {
        auth()->login(\App\Models\User::firstOrFail());
        $qs = request()->getQueryString();
        $to = $path === 'dashboard' ? '/' : '/' . ltrim($path, '/');
        return redirect($to . ($qs ? '?' . $qs : ''));
    })->where('path', '.*');
}
