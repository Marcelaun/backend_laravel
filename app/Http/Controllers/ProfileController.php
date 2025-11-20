<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Atualiza as informações do perfil (User + Professional).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validação
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Garante que o email é único, ignorando o próprio usuário
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],

            // Dados do Profissional
            'cpf' => ['nullable', 'string', 'max:14'],
            'registro_profissional' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        // 2. Atualiza a tabela 'users'
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null; // Se mudou o email, desvalida
        }

        $user->save();

        // 3. Atualiza a tabela 'professionals' (se for um profissional)
        if ($user->role === 'professional') {
            // updateOrCreate: Atualiza se existir, cria se não existir
            $user->professional()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'cpf' => $validated['cpf'],
                    'registro_profissional' => $validated['registro_profissional'],
                    'telefone' => $validated['telefone'],
                ]
            );
        }

        return response()->json(['message' => 'Perfil atualizado!', 'user' => $user->load('professional')]);
    }

    /**
     * Atualiza a senha do usuário.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // O Laravel verifica se a senha atual bate
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Senha alterada com sucesso!']);
    }
}
