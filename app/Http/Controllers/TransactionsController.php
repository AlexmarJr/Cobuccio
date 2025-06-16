<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pix;
use App\Models\PixFavorites;
use App\Models\TransactionsHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\BankAccount;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\TransactionsHistoryController;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    public function __construct()
    {
        $this->transactionsHistoryController = new TransactionsHistoryController();
    }

    public function pixTransfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'pix_key' => 'required|string',
        ]);

        $account = auth()->user()->accounts->first();
        if ($account->balance < $request->amount) {
            return response()->json(['message' => 'Saldo insuficiente.'], 422);
        }

        $account->balance -= $request->amount;
        $account->save();

        $receiver = Pix::where('key', $request->pix_key)
            ->where('status', 'active')
            ->first();
        if (!$receiver) {
            return response()->json(['message' => 'Chave Pix inválida ou inativa.'], 422);
        }
        $receiver = $receiver->account;
        $receiver->balance += $request->amount;
        $receiver->save();

        $this->transactionsHistoryController->saveTransaction('pix_transfer', $receiver->user, $request->amount);

        if ($request->add_to_favorites) {
            $pixFavorite = PixFavorites::where('user_id', Auth::id())
                ->where('pix_key', $request->pix_key)
                ->first();
            if (!$pixFavorite) {
                PixFavorites::Create(
                    [
                        'user_id' => Auth::id(),
                        'pix_key' => $request->pix_key,
                        'pix_type' => 'pix',
                        'name' => $receiver->user->name,
                        'cpf' => $receiver->user->cpf,
                    ],
                );
            }
        }

        return response()->json(['message' => 'Transferência Pix realizada com sucesso!']);

    }

   public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account' => 'required|string',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $account = auth()->user()->accounts->first();

                if ($account->balance < $request->amount) {
                    throw new \Exception('Saldo insuficiente.');
                }

                $receiver = BankAccount::where('account_number', $request->account)
                    ->where('status', 'active')
                    ->first();

                if (!$receiver) {
                    throw new \Exception('Conta inválida ou inativa.');
                }

                $account->balance -= $request->amount;
                $account->save();

                $receiver->balance += $request->amount;
                $receiver->save();

                $this->transactionsHistoryController->saveTransaction('transfer', $receiver->user, $request->amount);

                return response()->json(['message' => 'Transferência realizada com sucesso!']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getClient($accountId)
    {
        $account = BankAccount::where('account_number', $accountId)->where('status', 'active')->first();
        if (!$account) {
            return response()->json(['error' => 'Conta bancária não encontrada.'], 404);
        }

        return response()->json([
            'id' => $account->id,
            'name' => $account->user->name,
            'cpf' => $account->user->cpf,
        ]);
    }

    public function deposit(Request $request)
    {
        $request = request()->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $account = auth()->user()->accounts->first();
        $account->balance += $request['amount'];
        $account->save();

        $this->transactionsHistoryController->saveTransaction('deposit', null, $request['amount']);

        return response()->json(['message' => 'Depósito realizado com sucesso!']);
    }

    public function withdraw(Request $request)
    {
        $request = request()->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $account = auth()->user()->accounts->first();

        if ($account->balance < $request['amount']) {
            return response()->json(['error' => 'Saldo insuficiente.'], 422);
        }

        $account->balance -= $request['amount'];
        $account->save();

        $this->transactionsHistoryController->saveTransaction('withdraw', null, $request['amount']);

        return response()->json(['message' => 'Saque realizado com sucesso!']);
    }

    public function refund(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions_histories,id',
        ]);

        return DB::transaction(function () use ($request) {
            $transaction = TransactionsHistory::find($request->transaction_id);

            if (!$transaction || $transaction->status === 'refunded') {
                return response()->json(['message' => 'Transação inválida ou já reembolsada.'], 422);
            }

            $sender = User::find($transaction->user_id);
            $receiver = User::find($transaction->receiver);

            $senderAccount = $sender->accounts->first();
            $receiverAccount = $receiver->accounts->first();

            if ($receiverAccount->balance < $transaction->amount) {
                return response()->json(['message' => 'O destinatário não tem saldo suficiente para o estorno.'], 422);
            }

            $senderAccount->balance += $transaction->amount;
            $receiverAccount->balance -= $transaction->amount;

            $senderAccount->save();
            $receiverAccount->save();

            $transaction->status = 'refunded';
            $transaction->save();

            return response()->json(['message' => 'Reembolso realizado com sucesso.']);
        });
    }
}
