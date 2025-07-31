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
use App\Services\Olx\OlxService;
use App\Http\Controllers\RomimoController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ImobiliareController;
use App\Http\Controllers\OlxController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // User Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/users', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/users/{id}/update-role', [ProfileController::class, 'updateRole'])->name('users.update-role');

    // Properties Routes
    Route::resource('properties', PropertyController::class);
    Route::get('/scraper', [PropertyController::class, 'scraperView'])->name('properties.scraperView');
    Route::get('/delisted', [PropertyController::class, 'delistedView'])->name('properties.delistedView');
    Route::get('/portfolio', [PropertyController::class, 'portfolioView'])->name('properties.portfolioView');
    Route::get('/properties/{property}/assign', [PropertyController::class, 'assignToUser'])
        ->name('properties.assign');
    Route::get('/properties/{property}/delist', [PropertyController::class, 'delist'])
        ->name('properties.delist');
    Route::post('/properties/{id}/unlock', [PropertyController::class, 'unlock'])->name('properties.unlock');

    // Lead Routes
    Route::resource('leads', LeadController::class);
    Route::get('/lead-portfolio', [LeadController::class, 'portfolioView'])->name('leads.portfolioView');

    // Tickets Routes
    Route::resource('tickets', TicketController::class);

    // Gallery Routes
    Route::post('/images', [ImagesController::class, 'store'])->name('images.store');
    Route::post('/destroyAllImages/{property}', [ImagesController::class, 'deleteAll'])->name('images.deleteAll');
    Route::delete('/images/{image}', [ImagesController::class, 'destroy'])->name('images.destroy');

    // Comments Routes
    Route::post('/comments/{commentable_type}/{commentable_id}', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Export Routes
    Route::get('/exporters', [ExportController::class, 'index'])->name('exporters.index');
    Route::get('export/users', [ExportController::class, 'exportUsers'])->name('export.users');
    Route::get('export/leads', [ExportController::class, 'exportLeads'])->name('export.leads');
    Route::get('export/properties', [ExportController::class, 'exportProperties'])->name('export.properties');

    //Contracts
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('create/{type}', [ContractController::class, 'create'])->name('create');
        Route::post('contractStore', [ContractController::class, 'store'])->name('store');
        Route::get('{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('{contract}/pdf', [ContractController::class, 'exportPdf'])->name('pdf');
    });

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear.cache');
    Route::get('/runAgentSync', [SettingsController::class, 'runAgentSync'])->name('settings.agentsync');
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('logs.index');

    //Search Routes
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');


    //OlxAPI
    Route::prefix('olx')->group(function () {
        Route::get('auth-url', [OlxController::class, 'getAuthUrl']);
        Route::post('exchange-token', [OlxController::class, 'exchangeCode']);

        Route::post('ads', [OlxController::class, 'postAd'])->name('olx.postAd');
        Route::put('ads/{id}', [OlxController::class, 'updateAd']);
        Route::delete('ads/{id}', [OlxController::class, 'deleteAd']);
        Route::post('ads/{id}/activate', [OlxController::class, 'activateAd']);
        Route::post('ads/{id}/deactivate', [OlxController::class, 'deactivateAd']);
        Route::post('ads/{id}/promote', [OlxController::class, 'applyPromotion']);
        Route::get('callback', [OlxController::class, 'handleCallback']);

        Route::get('categories', [OlxController::class, 'getAllCategories']);
        Route::get('categories/{id}/attributes', [OlxController::class, 'getCategoryAttributes']);
    });


    //ImobiliareApi
    Route::post('/imobiliare/create', [ImobiliareController::class, 'createPayLoad'])->name('imobiliare.create');
    Route::post('/imobiliare/agent/update/{id}', [ImobiliareController::class, 'updateAgent'])->name('imobiliare.agent.update');;

    //Romimo
    Route::post('/romimo/create', [RomimoController::class, 'createPayLoad'])->name('romimo.create');
    Route::delete('/romimo/deactivate/{property}', [RomimoController::class, 'deactivateFromRomimo'])->name('romimo.deactivate');

    Route::get('/romimo/categories', [RomimoController::class, 'getCategories']);
    Route::get('/romimo/properties', [RomimoController::class, 'getProperties']);
});

require __DIR__.'/auth.php';
