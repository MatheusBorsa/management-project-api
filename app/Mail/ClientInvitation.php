<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ClientInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public Client $client;
    public User $invitedBy;
    public string $role;
    public string $invitedEmail;
    public string $acceptUrl;

    public function __construct(Client $client, User $invitedBy, string $role, string $invitedEmail)
    {
        $this->client = $client;
        $this->invitedBy = $invitedBy;
        $this->role = $role;
        $this->invitedEmail = $invitedEmail;

        $this->acceptUrl = URL::temporarySignedRoute(
            'client.invitation.accept',
            now()->addDays(7),
            [
                'client' => $client->id,
                'email' => $invitedEmail,
                'role' => $role
            ]
        );
    }

    public function envelope()
    {
        return new Envelope(
            subject: "You've been invited to collaborate on {$this->client->name}"
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.client-invitation',
            with: [
                'client' => $this->client,
                'invitedBy' => $this->invitedBy,
                'role' => $this->role,
                'acceptUrl' => $this->acceptUrl
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}
