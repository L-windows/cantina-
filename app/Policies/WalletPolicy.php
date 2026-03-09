<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        if ($user->role === 'admin' || $user->role === 'operator') {
            return true;
        }

        if ($user->role === 'student') {
            return $user->students()->where('id', $wallet->student_id)->exists();
        }

        // parents could be implemented later
        return false;
    }

    public function manage(User $user): bool
    {
        return in_array($user->role, ['admin','operator']);
    }
}
