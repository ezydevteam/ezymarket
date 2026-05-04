<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables that should include archival tracking.
     */
    private array $tables = [
        'tickets',
        'transactions',
        'refunds',
        'payouts',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Check if deleted_at already exists (standard SoftDeletes)
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }

                // Add polymorphic tracking for archival provenance
                $table->unsignedBigInteger('deleted_by_id')->nullable()->after('deleted_at');
                $table->string('deleted_by_type')->nullable()->after('deleted_by_id');
                
                // Add indexes for performance in Admin archive views
                $table->index(['deleted_by_id', 'deleted_by_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['deleted_by_id', 'deleted_by_type']);
                
                // We typically don't drop deleted_at in down if it was already there,
                // but for completeness in a new system, we can.
                // $table->dropSoftDeletes();
            });
        }
    }
};
