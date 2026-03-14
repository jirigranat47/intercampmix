<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\ParticipantSearchController::class, 'index'])->name('participant.search');

Route::get('/auth/{token}', [App\Http\Controllers\AuthController::class, 'login'])->name('auth.login');
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('auth.logout');

Route::middleware(['web'])->group(function () {
    Route::get('/admin/import', [App\Http\Controllers\Admin\ImportController::class, 'index'])->name('admin.import');
    Route::post('/admin/import', [App\Http\Controllers\Admin\ImportController::class, 'process'])->name('admin.import.process');
    Route::get('/admin/db', [App\Http\Controllers\Admin\DBViewerController::class, 'index'])->name('admin.db');
    Route::post('/admin/mix', [App\Http\Controllers\Admin\MixerController::class, 'runAlgorithm'])->name('admin.mix.process');
    Route::get('/admin/export', [App\Http\Controllers\Admin\MixerController::class, 'export'])->name('admin.export.process');
    Route::get('/admin/groups', [App\Http\Controllers\Admin\GroupsOverviewController::class, 'index'])->name('admin.groups');
    Route::get('/admin/stats', [App\Http\Controllers\Admin\StatsController::class, 'index'])->name('admin.stats');
    Route::get('/admin/tokens', [App\Http\Controllers\Admin\AdminTokenController::class, 'index'])->name('admin.tokens');
    Route::post('/admin/tokens', [App\Http\Controllers\Admin\AdminTokenController::class, 'store'])->name('admin.tokens.store');
    Route::delete('/admin/tokens/{id}', [App\Http\Controllers\Admin\AdminTokenController::class, 'destroy'])->name('admin.tokens.destroy');

    // Group Management (Contacts)
    Route::get('/admin/groups/search', [App\Http\Controllers\Admin\GroupManagementController::class, 'search'])->name('admin.groups.search');
    Route::get('/admin/groups/autocomplete', [App\Http\Controllers\Admin\GroupManagementController::class, 'autocomplete'])->name('admin.groups.autocomplete');
    Route::get('/admin/groups/{id}/edit', [App\Http\Controllers\Admin\GroupManagementController::class, 'edit'])->name('admin.groups.edit');
    Route::put('/admin/groups/{id}', [App\Http\Controllers\Admin\GroupManagementController::class, 'update'])->name('admin.groups.update');
});
