<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;

class WalletRepository
{
    public function createWalletAccount($loginUserId)
    {
        // Use firstOrCreate to prevent duplicate wallets in the database
        Wallet::firstOrCreate(
            ['user_id' => $loginUserId],
            [
                'balance' => 0,
                'deposit' => 0,
                'status' => 'active',
            ]
        );

        User::where('id', $loginUserId)->update(['wallet_is_created' => 1]);
    }
}
