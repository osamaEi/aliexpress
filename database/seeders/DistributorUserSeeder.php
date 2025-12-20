<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DistributorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a distributor user
        $distributor = User::create([
            'name' => 'Distributor User',
            'full_name' => 'Distributor User',
            'email' => 'distributor@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'distributor',
            'company_name' => 'Distribution Company LLC',
            'country' => 'AE',
            'phone' => '+971501234567',
            'is_verified' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Create wallet for distributor
        Wallet::create([
            'user_id' => $distributor->id,
            'balance' => 1000.00,
            'pending_balance' => 0.00,
            'currency' => 'AED',
            'is_active' => true,
        ]);

        $this->command->info('Distributor user created successfully!');
        $this->command->info('Email: distributor@example.com');
        $this->command->info('Password: password123');
    }
}
