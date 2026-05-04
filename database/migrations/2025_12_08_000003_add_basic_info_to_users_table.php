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
        // Add basic_info column
        Schema::table('users', function (Blueprint $table) {
            $table->text('basic_info')->nullable()->after('avatar');
        });

        // Migrate existing data from profile_* and profile_social_links columns to basic_info
        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $basicInfo = [];

                // Migrate profile fields
                if ($user->profile_cover) {
                    $basicInfo['cover'] = $user->profile_cover;
                }
                if ($user->profile_heading) {
                    $basicInfo['heading'] = $user->profile_heading;
                }
                if ($user->profile_description) {
                    $basicInfo['bio'] = $user->profile_description;
                }

                // Migrate social links from profile_social_links
                if ($user->profile_social_links) {
                    $socialLinks = json_decode($user->profile_social_links, true);
                    if (is_array($socialLinks)) {
                        // Map old keys to new keys (if they exist)
                        $basicInfo = array_merge($basicInfo, $socialLinks);
                    }
                }

                // Update user with basic_info
                if (!empty($basicInfo)) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['basic_info' => json_encode($basicInfo)]);
                }
            }
        });

        // Drop old columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_cover',
                'profile_heading',
                'profile_description',
                'profile_social_links'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add old columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_cover')->nullable()->after('avatar');
            $table->string('profile_heading')->nullable()->after('profile_cover');
            $table->text('profile_description')->nullable()->after('profile_heading');
            $table->text('profile_social_links')->nullable()->after('profile_description');
        });

        // Migrate data back from basic_info
        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                if ($user->basic_info) {
                    $basicInfo = json_decode($user->basic_info, true);
                    if (is_array($basicInfo)) {
                        $updates = [];

                        // Restore profile fields
                        if (isset($basicInfo['cover'])) {
                            $updates['profile_cover'] = $basicInfo['cover'];
                        }
                        if (isset($basicInfo['heading'])) {
                            $updates['profile_heading'] = $basicInfo['heading'];
                        }
                        if (isset($basicInfo['bio'])) {
                            $updates['profile_description'] = $basicInfo['bio'];
                        }

                        // Restore social links
                        $socialLinks = [];
                        $socialKeys = ['facebook', 'x', 'youtube', 'linkedin', 'instagram', 'pinterest'];
                        foreach ($socialKeys as $key) {
                            if (isset($basicInfo[$key])) {
                                $socialLinks[$key] = $basicInfo[$key];
                            }
                        }
                        if (!empty($socialLinks)) {
                            $updates['profile_social_links'] = json_encode($socialLinks);
                        }

                        if (!empty($updates)) {
                            DB::table('users')
                                ->where('id', $user->id)
                                ->update($updates);
                        }
                    }
                }
            }
        });

        // Drop basic_info column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('basic_info');
        });
    }
};
