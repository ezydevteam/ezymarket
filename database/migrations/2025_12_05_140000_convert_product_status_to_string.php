<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add a temporary column
        Schema::table('products', function (Blueprint $table) {
            $table->string('status_new')->nullable()->after('status');
        });

        // Map old integer values to new string values
        $statusMap = [
            1 => 'pending',
            2 => 'soft_rejected',
            3 => 'resubmitted',
            4 => 'approved',
            5 => 'hard_rejected',
            6 => 'restricted',
        ];

        // Update the new column with mapped values
        foreach ($statusMap as $oldValue => $newValue) {
            DB::table('products')
                ->where('status', $oldValue)
                ->update(['status_new' => $newValue]);
        }

        // Drop the old column and rename the new one
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // Also handle previous_status if it exists
        if (Schema::hasColumn('products', 'previous_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('previous_status_new')->nullable()->after('previous_status');
            });

            foreach ($statusMap as $oldValue => $newValue) {
                DB::table('products')
                    ->where('previous_status', $oldValue)
                    ->update(['previous_status_new' => $newValue]);
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('previous_status');
            });

            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('previous_status_new', 'previous_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map string values back to integers
        $statusMap = [
            'pending' => 1,
            'soft_rejected' => 2,
            'resubmitted' => 3,
            'approved' => 4,
            'hard_rejected' => 5,
            'restricted' => 6,
        ];

        Schema::table('products', function (Blueprint $table) {
            $table->integer('status_old')->nullable()->after('status');
        });

        foreach ($statusMap as $stringValue => $intValue) {
            DB::table('products')
                ->where('status', $stringValue)
                ->update(['status_old' => $intValue]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });

        // Handle previous_status
        if (Schema::hasColumn('products', 'previous_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('previous_status_old')->nullable()->after('previous_status');
            });

            foreach ($statusMap as $stringValue => $intValue) {
                DB::table('products')
                    ->where('previous_status', $stringValue)
                    ->update(['previous_status_old' => $intValue]);
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('previous_status');
            });

            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('previous_status_old', 'previous_status');
            });
        }
    }
};
