<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'name',
        'service_type',
        'organization_unit_id',
        'allow_return_for_correction',
        'is_active',
        'description',
    ];

    protected $casts = [
        'allow_return_for_correction' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class, 'approval_workflow_id')->orderBy('step_order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'approval_workflow_id');
    }
}

