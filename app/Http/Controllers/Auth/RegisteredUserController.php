<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Professional; // <-- 1. IMPORTAMOS O MODELO PROFESSIONAL
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        // 2. ADICIONAMOS NOSSOS CAMPOS CUSTOMIZADOS À VALIDAÇÃO
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:'.Professional::class],
            'registro_profissional' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        // 3. Criamos o Usuário (Login) com a 'role' de 'professional'
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'professional',
            'status' => 'pending',
        ]);

        // 4. CRIAMOS O PERFIL PROFESSIONAL E LIGAMOS AO USUÁRIO
        $user->professional()->create([
            'cpf' => $request->cpf,
            'registro_profissional' => $request->registro_profissional,
            'telefone' => $request->telefone,
        ]);

        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            \Log::error('Falha ao enviar e-mail de verificação: ' . $e->getMessage());
        }

        Auth::login($user);

        // O Breeze por padrão retorna um '204 No Content', o que é perfeito.
        return response()->noContent();
    }
}
