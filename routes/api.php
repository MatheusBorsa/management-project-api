<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientInvitationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

//Clients
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/clients', [ClientController::class, 'addClient']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::get('/clients', [ClientController::class, 'showAll']);
    Route::put('/clients/{id}', [ClientController::class, 'editClient']);
    Route::delete('/clients/{id}', [ClientController::class, 'removeClient']);
    Route::get('/clients/{id}/users', [ClientController::class, 'showCollaborators']);
    Route::put('/clients/{clientId}/users/{userId}', [ClientController::class, 'updateUserRole']);
    Route::delete('/clients/{clientId}/users/{userId}', [ClientController::class, 'removeCollaborator']);
});

//Invitations
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/clients/{clientId}/invitations', [ClientInvitationController::class, 'sendInvitation']);
    Route::post('/invitations/{token}/accept', [ClientInvitationController::class, 'acceptInvitation']);
    Route::get('/clients/{clientId}/invitations', [ClientInvitationController::class, 'getClientInvitations']);
    Route::post('/invitations/{invitationId}/resend', [ClientInvitationController::class, 'resendInvitation']);
    Route::delete('/invitations/{invitationId}', [ClientInvitationController::class, 'cancelInvitation']);
});
Route::get('/invitations/{token}', [ClientInvitationController::class, 'showInvitation']);
Route::post('/invitations/{token}/decline', [ClientInvitationController::class, 'declineInvitation']);

//Tasks
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/clients/{clientId}/tasks', [TaskController::class, 'createTask']);
    Route::put('/tasks/{id}', [TaskController::class, 'updateTask']);
    Route::get('/tasks/{id}', [TaskController::class, 'showTask']);
    Route::delete('/tasks/{id}', [TaskController::class, 'deleteTask']);
    Route::get('/clients/{clientId}/tasks', [TaskController::class, 'getAllTasks']);
});

//Dashboard
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});