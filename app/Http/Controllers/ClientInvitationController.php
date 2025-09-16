<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientInvitation;
use App\Enums\ClientUserRole;
use App\Utils\ApiResponseUtil;

class ClientInvitationController extends Controller
{
    public function sendInvitation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|string|in:' . implode(',', array_column(ClientUserRole::cases(), 'value'))
        ]);

        if ($validator->fails()) {
            return ApiResponseUtil::error(
                'Validation failed',
                ['error' => $validator->errors()],
                422    
            );
        }

        try {
            $currentUser = auth()->user();
            $invitedUserEmail = $request->email;

            $currentUserPivot = $currentUser->clients()
                ->where('client_id', $id)
                ->first();

            if (!$currentUserPivot || $currentUserPivot->pivot->role !== ClientUserRole::OWNER->value) {
                return ApiResponseUtil::error(
                    'You are not authorized',
                    null,
                    403
                );
            }

            $client = Client::findOrFail($id);

            $invitedUser = User::whereEmail($invitedUserEmail)->first();

            if ($invitedUser) {
                $isAlreadyAssociated = $invitedUser->clients()
                    ->where('client_id', $id)
                    ->exists();
                    
                if ($isAlreadyAssociated) {
                    return ApiResponseUtil::error(
                        'User is already collaborating with this client',
                        null,
                        409
                    );
                }
            }

            Mail::to($request->email)->send(new ClientInvitation(
                $client,
                $currentUser,
                $request->role,
                $request->email
            ));

            return ApiResponseUtil::success(
                'Invitation sent successfully',
                [
                    'client' => $client->name,
                    'invited_email' => $request->email,
                    'role' => $request->role,
                    'sent_by' => $currentUser->name
                ],
                200
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Failed to send invitation',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function acceptInvitation(Request $request): JsonResponse
    {
        try {
            $id = $request->get('client');
            $email = $request->get('email');
            $role = $request->get('role');

            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please create an account first',
                    'redirect_to_registration' => true,
                    'invitation_data' => [
                        'client_id' => $id,
                        'email' => $email,
                        'role' => $role
                    ]
                ], 302);
            }

            $client = Client::findOrFail($id);

            $existingAssociation = $user->clients()->where('client_id', $id)->exists();
            
            if ($existingAssociation) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already associated with this client'
                ], 409);
            }

            $user->clients()->attach($id, [
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully joined client collaboration',
                'data' => [
                    'client_name' => $client->name,
                    'role' => $role
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept invitation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
