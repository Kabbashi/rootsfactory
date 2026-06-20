<?php

use App\Http\Controllers\Auth\ConceptnoteSsoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SSO gegen den conceptnote-OIDC-Provider (Phase B).
Route::get('/auth/conceptnote/redirect', [ConceptnoteSsoController::class, 'redirect'])
    ->name('sso.conceptnote.redirect');
Route::get('/auth/conceptnote/callback', [ConceptnoteSsoController::class, 'callback'])
    ->name('sso.conceptnote.callback');
