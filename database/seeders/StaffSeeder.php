<?php

namespace Database\Seeders;

use App\Enums\Admin\AdminRole;
use App\Models\Admin;
use App\Models\Product\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Manager
        $manager = Admin::create([
            'firstname' => 'John',
            'lastname' => 'Manager',
            'username' => 'manager',
            'email' => 'manager@example.com',
            'role' => AdminRole::MANAGER->value,
            'status' => true,
            'password' => Hash::make('password123'),
            'google2fa_status' => 0,
        ]);

        // Create Accountant
        $accountant = Admin::create([
            'firstname' => 'Sarah',
            'lastname' => 'Accountant',
            'username' => 'accountant',
            'email' => 'accountant@example.com',
            'role' => AdminRole::ACCOUNTANT->value,
            'status' => true,
            'password' => Hash::make('password123'),
            'google2fa_status' => 0,
        ]);

        // Create Reviewer
        $reviewer = Admin::create([
            'firstname' => 'Mike',
            'lastname' => 'Reviewer',
            'username' => 'reviewer',
            'email' => 'reviewer@example.com',
            'role' => AdminRole::REVIEWER->value,
            'status' => true,
            'password' => Hash::make('password123'),
            'google2fa_status' => 0,
        ]);

        // Assign categories to reviewer (if categories exist)
        $categories = ProductCategory::limit(3)->pluck('id');
        if ($categories->isNotEmpty()) {
            $reviewer->categories()->sync($categories);
            $this->command->info("Assigned {$categories->count()} categories to reviewer");
        }

        $this->command->info('Staff seeded successfully!');
        $this->command->info('Login credentials (password for all: password123):');
        $this->command->info('- Manager: manager@example.com');
        $this->command->info('- Accountant: accountant@example.com');
        $this->command->info('- Reviewer: reviewer@example.com');
    }
}
