<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\ClientInvitation;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientInvitation as ClientInvitationMail;
use App\Enums\ClientUserRole;
use App\Utils\ApiResponseUtil;
use Illuminate\Support\Facades\DB;

class ClientInvitationController extends Controller
{   
    private function isClientOwner(Client $client, User $user): bool
    {
        return $client->users()
            ->where('user_id', $user->id)
            ->wherePivot('role', ClientUserRole::OWNER->value)
            ->exists();
    }
    public function sendInvitation(Request $request, $clientId)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:' . implode(',', array_column(ClientUserRole::cases(), 'value'))
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $client = Client::findOrFail($clientId);

            if (!$this->isClientOwner($client, $user)) {
                return ApiResponseUtil::error(
                    'You are not authorized',
                    null,
                    403
                );
            }

            $invitedUser = User::where('email', $request->email)->first();
            if ($invitedUser && $invitedUser->clients()->where('client_id', $clientId)->exists()) {
                return ApiResponseUtil::error(
                    'User is already collaborating with this client',
                    null,
                    409
                );
            }

            $existingInvitation = ClientInvitation::where('client_id', $clientId)
                ->where('email', $request->email)
                ->pending()
                ->first();

            if ($existingInvitation) {
                return ApiResponseUtil::error(
                    'Pending invitation already exists for this email',
                    null,
                    409
                );
            }

            ClientInvitation::where('client_id', $clientId)
                ->where('email', $request->email)
                ->update(['status' => 'expired']);

            $invitation = ClientInvitation::create([
                'client_id' => $clientId,
                'invited_by' => $user->id,
                'email' => $request->email,
                'role' => $request->role
            ]);

            Mail::to($request->email)->send(new ClientInvitationMail(
                $client,
                $user,
                $request->role,
                $request->email,
                $invitation
            ));

            DB::commit();

            return ApiResponseUtil::success(
                'Invitation sent successfully',
                [
                'invitation_id' => $invitation->id,
                'client' => $client->name,
                'invited_email' => $request->email,
                'role' => $request->role,
                'expires_at' => $invitation->expires_at,
                'token' => $invitation->token
                ],
                201
            );

        } catch (Exception $e) {
            DB::rollback();
            return ApiResponseUtil::error(
                'Failed to send invitation',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function showInvitation(string $token)
    {
        try {
            $invitation = ClientInvitation::with(['client', 'invitedBy'])
                ->where('token', $token)
                ->firstOrFail();
            
            if ($invitation->isExpired()) {
                return ApiResponseUtil::error(
                    'Invitation has expired',
                    null,
                    410
                );
            }

            if ($invitation->status !== 'pending') {
                return ApiResponseUtil::error(
                    'Invitation is no longer valid',
                    null,
                    410
                );
            }

            return ApiResponseUtil::success(
                'Invitation details retrieved',
                [
                    'id' => $invitation->id,
                    'client' => [
                        'id' => $invitation->client->id,
                        'name' => $invitation->client->name,
                        'description' => $invitation->client->description
                    ],
                    'invited_by' => [
                        'name' => $invitation->invitedBy->name,
                        'email' => $invitation->invitedBy->email
                    ],
                    'role' => $invitation->role,
                    'email' => $invitation->email,
                    'expires_at' => $invitation->expires_at,
                    'status' => $invitation->status
                ],
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Invitation not found',
                null,
                404
            );
        }
    }

    public function acceptInvitation(Request $request, string $token)
    {
        try {
            DB::beginTransaction();

            $invitation = ClientInvitation::with('client')
                ->where('token', $token)
                ->firstOrFail();

            if ($invitation->isExpired()) {
                return ApiResponseUtil::error(
                    'Invitation has expired',
                    null,
                    410
                );
            }

            if ($invitation->status !== 'pending') {
                return ApiResponseUtil::error(
                    'Invitation is no longer valid',
                    null,
                    410
                );
            }

            $user = $request->user();

            if ($user->email !== $invitation->email) {
                return ApiResponseUtil::error(
                    'This invitation was not sent to your email address',
                    null,
                    403
                );
            }

            if ($user->clients()->where('client_id', $invitation->client_id)->exists()) {
                return ApiResponseUtil::error(
                    'You are already associated with this client',
                    null,
                    409
                );
            }

            $user->clients()->attach($invitation->client_id, [
                'role' => $invitation->role,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $invitation->accept();

            DB::commit();

            return ApiResponseUtil::success(
                'Successfully joined client collaboration',
                [
                    'client' => [
                        'id' => $invitation->client->id,
                        'name' =>$invitation->client->name
                    ],
                    'role' => $invitation->role
                ]
            );

        } catch (Exception $e) {
            DB::rollback();
            return ApiResponseUtil::error(
                'Failed to accept invitation',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function declineInvitation(string $token)
    {
        try {
            $invitation = ClientInvitation::where('token', $token)->firstOrFail();

            if ($invitation->isExpired()) {
                return ApiResponseUtil::error(
                    'Invitation has expired',
                    null,
                    410
                );
            }

            if ($invitation->status !== 'pending') {
                return ApiResponseUtil::error(
                    'Invitation is no longer valid',
                    null,
                    410
                );
            }

            $invitation->decline();

            return ApiResponseUtil::success(
                'Invitation declined successfully'
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Invitation not found',
                null,
                404
            );
        }
    }
}
