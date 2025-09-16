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
}
