<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AskController;
use App\Http\Controllers\Auth\ConceptnoteSsoController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\ResearchController;
use Illuminate\Support\Facades\Route;

// The public front door is the network's published work.
Route::get('/', [PublicationController::class, 'index'])->name('publications.index');
Route::get('/publications/{publication}', [PublicationController::class, 'show'])->name('publications.show');

// About the network.
Route::get('/about', [AboutController::class, 'show'])->name('about');

// Research projects (public).
Route::get('/research', [ResearchController::class, 'index'])->name('research.index');
Route::get('/research/{project}', [ResearchController::class, 'show'])->name('research.show');

// The community of researchers and their profiles.
Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::get('/people/{user}', [PublicationController::class, 'person'])->name('people.show');

// Public Q&A grounded in our published publications, with cited sources.
Route::get('/ask', [AskController::class, 'ask'])->name('ask');

// SSO gegen den conceptnote-OIDC-Provider (Phase B).
Route::get('/auth/conceptnote/redirect', [ConceptnoteSsoController::class, 'redirect'])
    ->name('sso.conceptnote.redirect');
Route::get('/auth/conceptnote/callback', [ConceptnoteSsoController::class, 'callback'])
    ->name('sso.conceptnote.callback');
