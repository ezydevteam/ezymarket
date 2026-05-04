<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename the table from kyc_verifications to id_verifications
        Schema::rename('kyc_verifications', 'id_verifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename the table back from id_verifications to kyc_verifications
        Schema::rename('id_verifications', 'kyc_verifications');
    }
};
