<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PixController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\TransactionsHistoryController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->get('/user/balance', function () {
    return response()->json([
        'balance' => Auth::user()->accounts->first()->balance ?? 0
    ]);
});

Route::middleware('auth')->group(function () {
    // Rotas Pix
    Route::post('pix/save', [PixController::class, 'save'])->name('pix.save');
    Route::get('/pix/list', [PixController::class, 'list'])->name('pix.list');
    Route::post('/get-pix-client', [PixController::class, 'getClient']);
    Route::get('/get-pix-favorites', [PixController::class, 'getFavorites'])->name('getFavorites');
    Route::delete('/pix.delete/{id}', [PixController::class, 'deletePixKey'])->name('pix.delete');

    // Rotas Transações
    Route::get('/get-account/{id}', [TransactionsController::class, 'getClient']);
    Route::post('/deposit', [TransactionsController::class, 'deposit'])->name('transactions.deposit');
    Route::post('/withdraw', [TransactionsController::class, 'withdraw'])->name('transactions.withdraw');
    Route::post('/pixTransfer', [TransactionsController::class, 'pixTransfer'])->name('transactions.pixTransfer');
    Route::post('/transfer', [TransactionsController::class, 'transfer'])->name('transactions.transfer');
    Route::post('/refund', [TransactionsController::class, 'refund'])->name('transactions.refund');

    // Histórico de transações
    Route::get('/history.list', [TransactionsHistoryController::class, 'historyList'])->name('history.list');
});

require __DIR__.'/auth.php';


//API da newsapi, nao ta da forma correta,mas tambem nao tava muito afim de configurar o curl, mas so queria botar alguma coisa a mais.
Route::get('/noticias', function () {
    return Http::withoutVerifying()
        ->get('https://newsapi.org/v2/everything', [
            'q' => 'investimento',
            'language' => 'pt',
            'pageSize' => 10,
            'apiKey' => '896f24c6a2e34b679a06bdab61983b20 a',
        ])
        ->json();
});