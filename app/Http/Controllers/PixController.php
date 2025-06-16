<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PixController;
use App\Models\Pix;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Str;
use App\Models\PixFavorites;

class PixController extends Controller
{
    public function save(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:cpf,email,phone,random',
            'key' => 'required_unless:type,random',
        ]);

        if ($validated['type'] === 'cpf') {
            $cpf = preg_replace('/\D/', '', $validated['key']);
            if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) || !$this->isValidCPF($cpf)) {
                return response()->json(['errors' => ['key' => 'CPF inválido.']], 422);
            }
        }

        if ($validated['type'] === 'email' && !filter_var($validated['key'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['errors' => ['key' => 'Email inválido.']], 422);
        }

        if ($validated['type'] === 'phone' && strlen(preg_replace('/\D/', '', $validated['key'])) < 10) {
            return response()->json(['errors' => ['key' => 'Telefone inválido.']], 422);
        }

        if ($validated['type'] === 'random') {
            $validated['key'] = (string) Str::uuid();
        }

        $existingPix = Pix::where('key', $validated['key'])
            ->first();
        if ($existingPix) {
            return response()->json(['errors' => ['key' => 'Chave Pix já cadastrada.']], 422);
        }
        $pix = Pix::create([
            'type' => $validated['type'],
            'key' => $validated['key'],
            'account_id' => Auth::user()->accounts->first()->id,
        ]);

        return response()->json(['message' => 'Chave Pix salva com sucesso!']);
    }

    public function list()
    {
        $pixKeys = Pix::where('account_id', Auth::user()->accounts->first()->id)->get();
        return response()->json($pixKeys);
    }

    public function getClient(Request $request)
    {
        $pixKey = Pix::where('key', $request->input('key'))
            ->where('status', 'active')
            ->with(['account.user'])
            ->first();


        if ($pixKey->account_id == Auth::user()->accounts->first()->id) {
            return response()->json(['error' => 'Não pode tranferir para a propria conta!'], 422);
        }

        if (!$pixKey) {
            return response()->json(['error' => 'Chave Pix não encontrada.'], 404);
        }

        return response()->json($pixKey);
    }

    public function deletePixKey($id)
    {
        $pixKey = Pix::findOrFail($id);
        if ($pixKey->account_id !== Auth::user()->accounts->first()->id) {
            return response()->json(['error' => 'Chave Pix não pertence a sua conta.'], 403);
        }

        $pixKey->delete();
        return response()->json(['message' => 'Chave Pix excluída com sucesso!']);
    }

    private function isValidCPF($cpf)
    {
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    public function getFavorites()
    {
        $favorites = PixFavorites::where('user_id', Auth::id())
            ->get();

        return response()->json($favorites);
    }
}
