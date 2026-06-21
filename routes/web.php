<?php

use App\Http\Controllers\Auth\ConceptnoteSsoController;
use App\Http\Controllers\PublicationController;
use Illuminate\Support\Facades\Route;

// The public front door is the think-tank's published work.
Route::get('/', [PublicationController::class, 'index'])->name('publications.index');
Route::get('/publications/{idea}', [PublicationController::class, 'show'])->name('publications.show');

// SSO gegen den conceptnote-OIDC-Provider (Phase B).
Route::get('/auth/conceptnote/redirect', [ConceptnoteSsoController::class, 'redirect'])
    ->name('sso.conceptnote.redirect');
Route::get('/auth/conceptnote/callback', [ConceptnoteSsoController::class, 'callback'])
    ->name('sso.conceptnote.callback');
