<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false; // logged_at is timestamp

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'action',
        'module',
        'auditable_type',
        'auditable_id',
        'before_values',
        'after_values',
        'ip_address',
        'user_agent',
        'logged_at',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
        'logged_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

