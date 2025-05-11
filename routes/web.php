<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashborad
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // User Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/users', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/users/{id}/update-role', [ProfileController::class, 'updateRole'])->name('users.update-role');

    // Properties Routes
    Route::resource('properties', PropertyController::class);
    Route::post('/properties/{id}/unlock', [PropertyController::class, 'unlock'])->name('properties.unlock');

    // Lead Routes
    Route::resource('leads', LeadController::class);

    // Tickets Routes
    Route::resource('tickets', TicketController::class);

    // Gallery Routes
    Route::post('/images', [ImagesController::class, 'store'])->name('images.store');
    Route::delete('/images/{image}', [ImagesController::class, 'destroy'])->name('images.destroy');

    // Comments Routes
    Route::post('/comments/{commentable_type}/{commentable_id}', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Export Routes
    Route::get('/exporters', [ExportController::class, 'index'])->name('exporters.index');
    Route::get('export/users', [ExportController::class, 'exportUsers'])->name('export.users');
    Route::get('export/leads', [ExportController::class, 'exportLeads'])->name('export.leads');
    Route::get('export/properties', [ExportController::class, 'exportProperties'])->name('export.properties');

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear.cache');

    //Search Routes
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

});

require __DIR__.'/auth.php';
