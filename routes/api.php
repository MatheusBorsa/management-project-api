<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;

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
});

/*
GET	    /clients	                    List all clients for the logged-in user
POST	/clients	                    Create a new client
GET	    /clients/{id}	                Show client details
PATCH	/clients/{id}	                Update client info
DELETE	/clients/{id}	                Delete a client
POST	/clients/{id}/users	            Attach user to client (with role)
DELETE	/clients/{id}/users/{userId}	Detach user from client

GET	    /clients/{clientId}/users	    List users assigned to this client
POST	/clients/{clientId}/users	    Attach user to client (with role)
PATCH	/clients/{clientId}/users/{id}	Update role of a user in this client
DELETE	/clients/{clientId}/users/{id}	Remove user from client

GET	    /clients/{clientId}/tasks	List all tasks for a client
POST	/clients/{clientId}/tasks	Create a task for a client
GET	    /tasks/{id}	                Show task details
PATCH	/tasks/{id}	                Update task (title, desc, due_date, status)
DELETE	/tasks/{id}	                Delete a task
PATCH	/tasks/{id}/status	        Update only the status of a task

GET /tasks/{id}/history Get history for a task   
*/  