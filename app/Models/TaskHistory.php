<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskHistory extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'task_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at'
    ];

    public $timestamps = false;

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
