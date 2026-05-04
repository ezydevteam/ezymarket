<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * IMPORTANT: This migration renames all 'author' references to 'seller' throughout the database.
     * Make sure to back up your database before running this migration!
     */
    public function up(): void
    {
        // Rename author_taxes table to seller_taxes
        if (Schema::hasTable('author_taxes')) {
            Schema::rename('author_taxes', 'seller_taxes');
        }

        // Rename is_seller column to is_seller in users table (keeping same values 0/1)
        if (Schema::hasColumn('users', 'is_seller')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_seller', 'is_seller');
            });
        }

        // Rename is_featured_seller to is_featured_seller in users table
        if (Schema::hasColumn('users', 'is_featured_seller')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_featured_seller', 'is_featured_seller');
            });
        }

        // Rename seller_type to seller_type in users table
        if (Schema::hasColumn('users', 'seller_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('seller_type', 'seller_type');
            });
        }

        // Rename seller_id columns to seller_id in various tables
        $tablesWithAuthorId = [
            'products',
            'product_comments',
            'product_reviews',
            'refunds',
            'sales',
            'transactions',
            'withdrawals',
            'referrals',
        ];

        foreach ($tablesWithAuthorId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'seller_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('seller_id', 'seller_id');
                });
            }
        }

        // Rename seller_earning columns to seller_earning
        $tablesWithAuthorEarning = [
            'sales',
            'transactions',
        ];

        foreach ($tablesWithAuthorEarning as $tableName) {
            if (Schema::hasColumn($tableName, 'seller_earning')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('seller_earning', 'seller_earning');
                });
            }
        }

        // Rename author_fee columns to seller_fee
        $tablesWithAuthorFee = [
            'sales',
            'referral_earnings',
            'support_earnings',
            'premium_earnings',
            'transactions',
        ];

        foreach ($tablesWithAuthorFee as $tableName) {
            if (Schema::hasColumn($tableName, 'author_fee')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('author_fee', 'seller_fee');
                });
            }
        }

        // Rename author_tax columns to seller_tax
        $tablesWithAuthorTax = [
            'sales',
            'transactions',
        ];

        foreach ($tablesWithAuthorTax as $tableName) {
            if (Schema::hasColumn($tableName, 'author_tax')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('author_tax', 'seller_tax');
                });
            }
        }

        // Update badge aliases in badges table
        DB::table('badges')->where('alias', 'author-level-badge')->update(['alias' => 'seller-level-badge']);
        DB::table('badges')->where('alias', 'exclusive-author')->update(['alias' => 'exclusive-seller']);
        DB::table('badges')->where('alias', 'featured-author')->update(['alias' => 'featured-seller']);

        // Update page slugs
        DB::table('pages')->where('slug', 'become-an-author')->update(['slug' => 'become-a-seller']);
        DB::table('pages')->where('slug', 'author-terms')->update(['slug' => 'seller-terms']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename seller_taxes table back to author_taxes
        if (Schema::hasTable('seller_taxes')) {
            Schema::rename('seller_taxes', 'author_taxes');
        }

        // Rename is_seller column back to is_seller in users table
        if (Schema::hasColumn('users', 'is_seller')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_seller', 'is_seller');
            });
        }

        // Rename is_featured_seller back to is_featured_seller in users table
        if (Schema::hasColumn('users', 'is_featured_seller')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_featured_seller', 'is_featured_seller');
            });
        }

        // Rename seller_type back to seller_type in users table
        if (Schema::hasColumn('users', 'seller_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('seller_type', 'seller_type');
            });
        }

        // Rename seller_id columns back to seller_id in various tables
        $tablesWithSellerId = [
            'products',
            'product_comments',
            'product_reviews',
            'refunds',
            'sales',
            'transactions',
            'withdrawals',
            'referrals',
        ];

        foreach ($tablesWithSellerId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'seller_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('seller_id', 'seller_id');
                });
            }
        }

        // Rename seller_earning columns back to seller_earning
        $tablesWithSellerEarning = [
            'sales',
            'transactions',
        ];

        foreach ($tablesWithSellerEarning as $tableName) {
            if (Schema::hasColumn($tableName, 'seller_earning')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('seller_earning', 'seller_earning');
                });
            }
        }

        // Rename seller_fee columns back to author_fee
        $tablesWithSellerFee = [
            'sales',
            'referral_earnings',
            'support_earnings',
            'premium_earnings',
            'transactions',
        ];

        foreach ($tablesWithSellerFee as $tableName) {
            if (Schema::hasColumn($tableName, 'seller_fee')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('seller_fee', 'author_fee');
                });
            }
        }

        // Rename seller_tax columns back to author_tax
        $tablesWithSellerTax = [
            'sales',
            'transactions',
        ];

        foreach ($tablesWithSellerTax as $tableName) {
            if (Schema::hasColumn($tableName, 'seller_tax')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('seller_tax', 'author_tax');
                });
            }
        }

        // Revert badge aliases in badges table
        DB::table('badges')->where('alias', 'seller-level-badge')->update(['alias' => 'author-level-badge']);
        DB::table('badges')->where('alias', 'exclusive-seller')->update(['alias' => 'exclusive-author']);
        DB::table('badges')->where('alias', 'featured-seller')->update(['alias' => 'featured-author']);

        // Revert page slugs
        DB::table('pages')->where('slug', 'become-a-seller')->update(['slug' => 'become-an-author']);
        DB::table('pages')->where('slug', 'seller-terms')->update(['slug' => 'author-terms']);
    }
};
