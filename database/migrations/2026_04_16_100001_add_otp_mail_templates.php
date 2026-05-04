<?php
/**
 * Migration: Add OTP Mail Templates
 * 
 * Adds customizable mail templates for different OTP verification flows.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();
        $shortcodes = json_encode(['otp', 'user_name', 'expiry_minutes', 'site_name']);

        $templates = [
            [
                'alias' => 'registration_otp',
                'name' => 'Registration OTP Verification',
                'subject' => 'Verify your registration on {{site_name}}',
                'content' => $this->getTemplateContent(
                    'Welcome to {{site_name}}!',
                    'Thank you for joining us. Please use the following code to verify your account:'
                ),
                'shortcodes' => $shortcodes,
                'is_active' => 1,
            ],
            [
                'alias' => 'password_reset_otp',
                'name' => 'Password Reset OTP',
                'subject' => 'Your password reset code for {{site_name}}',
                'content' => $this->getTemplateContent(
                    'Password Reset Request',
                    'We received a request to reset your password. Use the code below to proceed:'
                ),
                'shortcodes' => $shortcodes,
                'is_active' => 1,
            ],
            [
                'alias' => 'email_change_otp',
                'name' => 'Email Change OTP Verification',
                'subject' => 'Confirm your new email on {{site_name}}',
                'content' => $this->getTemplateContent(
                    'Email Change Verification',
                    'You are changing your email address. Please use the following code to verify your new email:'
                ),
                'shortcodes' => $shortcodes,
                'is_active' => 1,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('mail_templates')->updateOrInsert(
                ['alias' => $template['alias']],
                $template
            );
        }
    }

    /**
     * Get default HTML structure for OTP emails.
     */
    private function getTemplateContent(string $title, string $message): string
    {
        return ' <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
            <h2 style="color: #2D3748; margin-bottom: 20px;">' . $title . '</h2>
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 25px;">Hello {{user_name}},</p>
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 25px;">' . $message . '</p>
            <div style="background: #F7FAFC; border: 1px solid #E2E8F0; padding: 30px; text-align: center; font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #1A202C; border-radius: 12px; margin: 30px 0;">
                {{otp}}
            </div>
            <p style="font-size: 14px; color: #718096; margin-bottom: 20px;">
                This code will expire in {{expiry_minutes}} minutes for your security.
            </p>
            <p style="font-size: 14px; color: #A0AEC0;">
                If you did not request this code, please ignore this email or contact support if you have concerns.
            </p>
            <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 30px 0;">
            <p style="font-size: 12px; color: #A0AEC0; text-align: center;">
                &copy; {{site_name}}. All rights reserved.
            </p>
        </div> ';
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mail_templates')->whereIn('alias', [
            'registration_otp',
            'password_reset_otp',
            'email_change_otp'
        ])->delete();
    }
};
