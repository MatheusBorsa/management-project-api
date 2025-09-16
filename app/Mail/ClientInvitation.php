<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\User;
use App\Models\ClientInvitation as ClientInvitationModel;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientInvitation extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public function __construct(
        public Client $client,
        public User $invitedBy,
        public string $role,
        public string $invitedEmail,
        public ?ClientInvitationModel $invitation = null
    ) {}

    public function build()
    {
        $acceptUrl = $this->invitation 
            ? config('app.frontend_url') . '/invitations/' . $this->invitation->token
            : config('app.frontend_url') . '/register?email=' . urlencode($this->invitedEmail);

        return $this->subject("You're invited to collaborate on {$this->client->name}")
                    ->view('emails.client-invitation')
                    ->with([
                        'clientName' => $this->client->name,
                        'inviterName' => $this->invitedBy->name,
                        'role' => $this->role,
                        'invitedEmail' => $this->invitedEmail,
                        'acceptUrl' => $acceptUrl,
                        'expiresAt' => $this->invitation?->expires_at
                    ]);
    }
}