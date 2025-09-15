<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientInvitationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/client-invitation/accept', [ClientInvitationController::class, 'acceptInvitation'])
    ->name('client.invitation.accept')
    ->middleware('signed');