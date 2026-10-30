<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_workflow_id',
        'step_order',
        'stage_type',
        'role_name',
        'specific_user_id',
        'step_title',
        'can_edit',
        'can_reject',
        'can_return',
    ];

    protected $casts = [
        'can_edit' => 'boolean',
        'can_reject' => 'boolean',
        'can_return' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function specificUser()
    {
        return $this->belongsTo(User::class, 'specific_user_id');
    }
}

