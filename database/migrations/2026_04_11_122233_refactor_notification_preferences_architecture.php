<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Settings;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new column to users table if it doesn't exist
        if (!Schema::hasColumn('users', 'notification_preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('notification_preferences')->nullable()->after('status');
            });
        }

        // Drop old table
        Schema::dropIfExists('notification_preferences');

        // Seed global notification settings
        $settingsData = [
            'admin_settings' => [
                'seller' => [
                    'new_follower' => true,
                    'new_badge' => true,
                    'product_favorite' => true,
                    'payment_confirmation' => true,
                    'purchase_confirmation' => true,
                    'sales_earning' => true,
                    'product_sold' => true,
                    'product_approved' => true,
                    'product_rejection' => true,
                    'product_update_approved' => true,
                    'product_update_rejected' => true,
                    'product_comment' => true,
                    'product_comment_reply' => true,
                    'product_review' => true,
                    'product_review_reply' => true,
                    'refund_request' => true,
                    'refund_reply' => true,
                    'new_support_ticket' => true,
                    'support_ticket_response' => true,
                    'support_ticket_status' => true,
                    'transaction_cancelled' => true,
                    'withdrawal_method_update' => true,
                    'withdrawal_submitted' => true,
                    'withdrawal_returned' => true,
                    'withdrawal_completed' => true,
                    'withdrawal_cancelled' => true,
                    'withdrawal_approved' => true,
                    'id_verification_status_update' => true,
                    'password_reset' => true,
                    'password_update' => true,
                    'email_update' => true,
                    'login_success' => true,
                    'login_attempt' => true,
                    'premium_about_to_expire' => true,
                    'premium_expired' => true
                ],
                'user' => [
                    'new_follower' => true,
                    'new_badge' => true,
                    'new_product' => true,
                    'product_update' => true,
                    'product_discount' => true,
                    'product_changelog' => true,
                    'payment_confirmation' => true,
                    'purchase_confirmation' => true,
                    'download_ready' => true,
                    'product_comment_reply' => true,
                    'product_review_reply' => true,
                    'refund_reply' => true,
                    'refund_status' => true,
                    'new_support_ticket' => true,
                    'support_ticket_response' => true,
                    'support_ticket_status' => true,
                    'transaction_cancelled' => true,
                    'id_verification_status_update' => true,
                    'become_seller' => true,
                    'password_reset' => true,
                    'password_update' => true,
                    'email_update' => true,
                    'email_verification' => true,
                    'login_success' => true,
                    'login_attempt' => true,
                    'premium_about_to_expire' => true,
                    'premium_expired' => true
                ]
            ],
            'preference_groups' => [
                'Account & Security' => [
                    'password_reset', 'password_update', 'email_update', 'email_verification', 'login_success', 'login_attempt', 'id_verification_status_update', 'become_seller'
                ],
                'Purchases & Transactions' => [
                    'payment_confirmation', 'purchase_confirmation', 'transaction_cancelled', 'download_ready'
                ],
                'Products' => [
                    'product_sold', 'product_approved', 'product_rejection', 'product_update_approved', 'product_update_rejected', 'new_product', 'product_update', 'product_discount', 'product_changelog', 'product_favorite'
                ],
                'Earnings & Payouts' => [
                    'sales_earning', 'withdrawal_method_update', 'withdrawal_submitted', 'withdrawal_returned', 'withdrawal_completed', 'withdrawal_cancelled', 'withdrawal_approved'
                ],
                'Interactions & Community' => [
                    'new_follower', 'new_badge', 'product_comment', 'product_comment_reply', 'product_review', 'product_review_reply'
                ],
                'Support & Refunds' => [
                    'new_support_ticket', 'support_ticket_response', 'support_ticket_status', 'refund_request', 'refund_reply', 'refund_status'
                ],
                'Premium Features' => [
                    'premium_about_to_expire', 'premium_expired'
                ]
            ]
        ];

        Settings::updateOrCreate(
            ['key' => 'notification_settings'],
            ['value' => $settingsData]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('event');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }
};
