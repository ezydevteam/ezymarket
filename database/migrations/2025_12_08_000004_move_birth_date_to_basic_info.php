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
        // Migrate birth_date data to basic_info
        DB::table('users')->whereNotNull('birth_date')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $basicInfo = json_decode($user->basic_info, true) ?? [];
                $basicInfo['birth_date'] = $user->birth_date;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['basic_info' => json_encode($basicInfo)]);
            }
        });

        // Drop birth_date column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add birth_date column
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('address');
        });

        // Migrate data back from basic_info
        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                if ($user->basic_info) {
                    $basicInfo = json_decode($user->basic_info, true);
                    if (isset($basicInfo['birth_date'])) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['birth_date' => $basicInfo['birth_date']]);
                    }
                }
            }
        });
    }
};
