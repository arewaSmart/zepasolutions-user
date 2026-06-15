<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    /**
     * Generate a unique 12-character reference number.
     */
    private function generateReferenceNumber(): string
    {
        $characters = '123456123456789071234567890890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $reference = '';

        for ($i = 0; $i < 12; $i++) {
            $reference .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $reference;
    }

    /**
     * Create a new transaction.
     */
    public function createTransaction(int $userId, float $amount): Transaction
    {
        $user = Auth::user();

        return Transaction::create([
            'user_id' => $userId,
            'payer_name' => $user->first_name.' '.$user->last_name,
            'payer_email' => $user->email,
            'payer_phone' => $user->phone_number,
            'referenceId' => $this->generateReferenceNumber(),
            'service_type' => 'Bonus Claim',
            'service_description' => 'Wallet credited with ₦'.number_format($amount, 2),
            'amount' => $amount,
            'gateway' => 'Wallet',
            'status' => 'Approved',
            'type' => 'credit',
        ]);
    }
}
