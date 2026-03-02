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
Route::post('/search', [App\Http\Controllers\ParticipantSearchController::class, 'search'])->name('participant.search.submit');

Route::get('/admin/import', [App\Http\Controllers\Admin\ImportController::class, 'index'])->name('admin.import');
Route::post('/admin/import', [App\Http\Controllers\Admin\ImportController::class, 'process'])->name('admin.import.process');

Route::get('/admin/db', [App\Http\Controllers\Admin\DBViewerController::class, 'index'])->name('admin.db');

Route::post('/admin/mix', [App\Http\Controllers\Admin\MixerController::class, 'runAlgorithm'])->name('admin.mix.process');
Route::get('/admin/export', [App\Http\Controllers\Admin\MixerController::class, 'export'])->name('admin.export.process');
Route::get('/admin/groups', [App\Http\Controllers\Admin\GroupsOverviewController::class, 'index'])->name('admin.groups');
Route::get('/admin/stats', [App\Http\Controllers\Admin\StatsController::class, 'index'])->name('admin.stats');
