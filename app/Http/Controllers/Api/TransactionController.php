<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin' || $user->role === 'operator') {
            $query = Transaction::query();
            if ($request->query('wallet_id')) {
                $query->where('wallet_id', $request->query('wallet_id'));
            }
            return TransactionResource::collection($query->latest()->paginate(15));
        }

        // students/parents: show only their student's wallet transactions
        if ($user->role === 'student' && $user->student) {
            $wallet = $user->student->wallet;
            if (!$wallet) {
                return response()->json(['data' => []]);
            }
            return TransactionResource::collection($wallet->transactions()->latest()->paginate(15));
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function show($id)
    {
        $tx = Transaction::findOrFail($id);
        return new TransactionResource($tx);
    }

    public function store(TransactionStoreRequest $request)
    {
        $wallet = Wallet::findOrFail($request->wallet_id);

        // Only operators/admins can create purchase/refund transactions
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'operator'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $amount = floatval($request->amount);
        if ($request->type === 'purchase') {
            if ($wallet->balance < $amount) {
                return response()->json(['message' => 'Insufficient balance'], 400);
            }
            $wallet->balance -= $amount;
        } elseif ($request->type === 'refund') {
            $wallet->balance += $amount;
        }

        $wallet->save();

        $tx = Transaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return (new TransactionResource($tx))->response()->setStatusCode(201);
    }
}
