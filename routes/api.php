<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientInvitationController;
use App\Http\Controllers\TaskController;


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

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/client/{id}/invite', [ClientInvitationController::class, 'sendInvitation'])
        ->name('client.invitation.send');
});

//Tasks
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/clients/{clientId}/tasks', [TaskController::class, 'createTask']);
});
/*
POST	/clients/{clientId}/tasks	Create a task for a client
PATCH	/tasks/{id}	                Update task (title, desc, due_date, status)
GET	    /tasks/{id}	                Show task details
DELETE	/tasks/{id}	                Delete a task

GET	    /clients/{clientId}/tasks	List all tasks for a client
PATCH	/tasks/{id}/status	        Update only the status of a task

GET /tasks/{id}/history Get history for a task   
*/  