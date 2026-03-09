<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;

class WalletController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        if ($user->role === 'student' && $user->student) {
             return response()->json($user->student->wallet);
        }
        // Admin or parents could view multiple wallets, simplified for now
        return response()->json(['message' => 'For students only or specify wallet'], 400);
    }

    public function history(Request $request) {
        // Assume student view, or pass wallet_id for parents
        $wallet_id = $request->query('wallet_id');
        
        $query = Transaction::query();
        if ($wallet_id) {
            $query->where('wallet_id', $wallet_id);
        }

        return response()->json($query->latest()->get());
    }

    public function topup(Request $request) {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $wallet = Wallet::findOrFail($request->wallet_id);
        $wallet->balance += $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'type' => 'topup',
            'description' => 'Recarga de saldo'
        ]);

        return response()->json([
            'message' => 'Topup successful',
            'wallet' => $wallet
        ]);
    }
}
