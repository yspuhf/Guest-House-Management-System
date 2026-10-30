<?php

namespace Database\Seeders;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflow = ApprovalWorkflow::updateOrCreate(
            ['name' => 'Standard Academic & Official Workflow'],
            [
                'service_type' => 'Guest House Booking',
                'organization_unit_id' => 1,
                'allow_return_for_correction' => true,
                'is_active' => true,
                'description' => 'Two-tier verification and sanction workflow for university accommodation',
            ]
        );

        ApprovalStep::updateOrCreate(
            ['approval_workflow_id' => $workflow->id, 'step_order' => 1],
            [
                'stage_type' => 'verifier',
                'role_name' => 'verifier',
                'specific_user_id' => 4,
                'step_title' => 'Departmental Document & Eligibility Verification',
                'can_edit' => true,
                'can_reject' => true,
                'can_return' => true,
            ]
        );

        ApprovalStep::updateOrCreate(
            ['approval_workflow_id' => $workflow->id, 'step_order' => 2],
            [
                'stage_type' => 'approver',
                'role_name' => 'approver',
                'specific_user_id' => 5,
                'step_title' => 'Dean / Controlling Authority Sanction',
                'can_edit' => false,
                'can_reject' => true,
                'can_return' => true,
            ]
        );
    }
}

