<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClientUser extends Pivot
{
    protected $table = 'client_user';

    protected $fillable = [
        'client_id',
        'user_id',
        'role'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
