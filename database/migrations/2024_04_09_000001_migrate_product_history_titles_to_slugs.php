<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mapping = [
            'Submission' => 'submission',
            'Trust Submission' => 'trust_submission',
            'Submission Approved' => 'submission_approved',
            'Resubmission' => 'resubmission',
            'Resubmission Approved' => 'resubmission_approved',
            'Soft Rejection' => 'soft_rejection',
            'Hard Rejection' => 'hard_rejection',
            'Update Submission' => 'update_submission',
            'Trust Update' => 'trust_update',
            'Update Approved' => 'update_approved',
            'Update Rejected' => 'update_rejected',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('product_histories')
                ->where('title', $old)
                ->update(['title' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $mapping = [
            'submission' => 'Submission',
            'trust_submission' => 'Trust Submission',
            'submission_approved' => 'Submission Approved',
            'resubmission' => 'Resubmission',
            'resubmission_approved' => 'Resubmission Approved',
            'soft_rejection' => 'Soft Rejection',
            'hard_rejection' => 'Hard Rejection',
            'update_submission' => 'Update Submission',
            'trust_update' => 'Trust Update',
            'update_approved' => 'Update Approved',
            'update_rejected' => 'Update Rejected',
        ];

        foreach ($mapping as $new => $old) {
            DB::table('product_histories')
                ->where('title', $new)
                ->update(['title' => $old]);
        }
    }
};
