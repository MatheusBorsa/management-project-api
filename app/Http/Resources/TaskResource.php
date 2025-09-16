<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ],
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline,
            'assigned_to' => $this->assigned_to,
            'assigned_user' => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ] : null,
        ];
    }
}
