<?php

namespace App\Http\Controllers;


use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Utils\ApiResponseUtil;
use App\Enums\ClientUserRole;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientController extends Controller
{
    public function addClient(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'contact_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:30',
                'notes' => 'required|string'
            ]);

            $client = Client::create($validatedData);

            $request->user()->clients()->attach($client->id, [
                'role' => ClientUserRole::OWNER->value,
            ]);

            return ApiResponseUtil::success(
                'Client created successfully',
                [
                    'client' => $client,
                    'Owner' => $request->user()
                ],
                201
            );

        } catch (ValidationException $e) {
            return ApiResponseUtil::error(
                'Validation Error',
                $e->errors(),
                422
            );
        
        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Server Error',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $client = Client::with(['users' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email')
                    ->withPivot('role');
            }])->findOrFail($id);

            return ApiResponseUtil::success(
                'Client retrieved successfully',
                $client,
                200
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Error retrieving client',
                ['error' => $e->getMessage()],
                500
            );

        }
    }

    public function showAll(Request $request)
    {
        try {
            $user = $request->user();

            $clients = $user->clients()->with(['users' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email')
                    ->withPivot('role');
            }])->get();

            return ApiResponseUtil::success(
                'Clients retrieved successfully',
                $clients,
                200
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Error retrieving clients',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function editClient(Request $request, $id)
    {
        try {
            $user = $request->user();

            $client = $user->clients()->findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'contact_name' => 'sometimes|nullable|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'phone' => 'sometimes|required|string|max:30',
                'notes' => 'sometimes|required|string'
            ]);

            $client->update($validatedData);

            return ApiResponseUtil::success(
                'Client updated successfully',
                $client,
                200
            );

        } catch (ModelNotFoundException $e) {
            return ApiResponseUtil::error(
                'Client not found or access denied',
                ['error' => $e->getMessage()],
                404
            );
        }
    }
}
