<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MarketerUserSeeder extends Seeder
{
    /**
     * Seed a demo marketer (مسوق) account. Idempotent.
     */
    public function run(): void
    {
        $marketer = User::firstOrCreate(
            ['email' => 'marketer@example.com'],
            [
                'name' => 'Marketer User',
                'full_name' => 'Marketer User',
                'password' => Hash::make('password123'),
                'user_type' => 'marketer',
                'country' => 'AE',
                'phone' => '+971502345678',
                'is_verified' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Ensure the marketer type/flags are correct even if the row already existed
        if ($marketer->user_type !== 'marketer') {
            $marketer->update(['user_type' => 'marketer']);
        }

        // Give the marketer a wallet
        Wallet::firstOrCreate(
            ['user_id' => $marketer->id],
            [
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'AED',
                'is_active' => true,
            ]
        );

        $this->command->info('Marketer user created successfully!');
        $this->command->info('Email: marketer@example.com');
        $this->command->info('Password: password123');
    }
}
