<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationUnit extends Model
{
    protected $fillable = ['code', 'name', 'type', 'contact_email', 'contact_phone', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'organization_unit_id');
    }

    public function workflows()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'organization_unit_id');
    }
}

