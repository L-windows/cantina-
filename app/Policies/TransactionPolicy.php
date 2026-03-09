<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        if (in_array($user->role, ['admin','operator'])) {
            return true;
        }

        if ($user->role === 'student') {
            return $user->students()->whereHas('wallet', function ($q) use ($transaction) {
                $q->where('id', $transaction->wallet_id);
            })->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin','operator']);
    }
}
