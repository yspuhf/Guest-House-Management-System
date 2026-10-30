<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['event_key', 'title', 'subject', 'body_template', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

