<?php
/**
 * Migration: Remove Legacy Mail Templates and Reorder IDs
 * 
 * Removes legacy templates and reorders the table so OTP templates are IDs 1, 2, 3.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fetch all existing templates excluding legacy ones
        $templates = DB::table('mail_templates')
            ->whereNotIn('alias', ['password_reset', 'email_verification'])
            ->get();

        // 2. Separate OTP templates from others
        $otpAliases = ['registration_otp', 'password_reset_otp', 'email_change_otp'];
        
        $otpTemplates = [];
        $otherTemplates = [];

        foreach ($templates as $template) {
            if (in_array($template->alias, $otpAliases)) {
                $otpTemplates[$template->alias] = $template;
            } else {
                $otherTemplates[] = $template;
            }
        }

        // 3. Perform the reorder safely
        Schema::disableForeignKeyConstraints();
        
        DB::table('mail_templates')->truncate();

        // 4. Insert OTP templates with explicit IDs 1, 2, 3
        $orderedOtps = ['registration_otp', 'password_reset_otp', 'email_change_otp'];
        foreach ($orderedOtps as $index => $alias) {
            if (isset($otpTemplates[$alias])) {
                $data = (array)$otpTemplates[$alias];
                $data['id'] = $index + 1;
                DB::table('mail_templates')->insert($data);
            }
        }

        // 5. Insert all other templates (ID will auto-increment from 4)
        foreach ($otherTemplates as $template) {
            $data = (array)$template;
            unset($data['id']); // Let auto-increment handle it
            DB::table('mail_templates')->insert($data);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not easily reversible due to truncate and ID shifts
    }
};
