<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionsHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class TransactionsHistoryController extends Controller
{
    public function saveTransaction($type, $receiver, $amount)
    {
        $transaction = TransactionsHistory::create([
            'transaction_type' => $type,
            'receiver' => $receiver->id ?? null,
            'amount' => $amount,
            'user_id' => Auth::id(),
        ]);
    }

    public function historyList()
    {
       $transactions = TransactionsHistory::with(['user', 'receiver'])
            ->where('user_id', auth()->id())
            ->orWhere('receiver', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($transactions);
    }
}
