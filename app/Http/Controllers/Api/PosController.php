<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function getStudentByQrCode($qr_code)
    {
        $student = Student::where('qr_code', $qr_code)
                    ->with(['wallet', 'parentalControl'])
                    ->firstOrFail();

        return response()->json([
            'student' => $student
        ]);
    }

    public function purchase(Request $request) 
    {
        $request->validate([
            'qr_code' => 'required|string',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $student = Student::where('qr_code', $request->qr_code)->firstOrFail();
        $wallet = Wallet::where('student_id', $student->id)->firstOrFail();
        
        $products = Product::whereIn('id', $request->product_ids)->get();
        $totalAmount = $products->sum('price');

        // Note: Missing parental control check here for simplicity for prototype

        if ($wallet->balance < $totalAmount) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        DB::beginTransaction();

        try {
            $wallet->balance -= $totalAmount;
            $wallet->save();

            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $request->user()->id, // The POS Operator
                'amount' => -$totalAmount,
                'type' => 'purchase',
                'description' => 'Compra na cantina: ' . $products->pluck('name')->join(', ')
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Purchase successful',
                'transaction' => $transaction,
                'new_balance' => $wallet->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaction failed', 'error' => $e->getMessage()], 500);
        }
    }
}
