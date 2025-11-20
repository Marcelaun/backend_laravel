<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Precisamos do modelo User

class AdminController extends Controller
{
    /**
     * Busca todos os profissionais cadastrados para o painel admin.
     */
    public function getProfessionals()
    {
        // Busca todos os usuários onde a 'role' é 'professional'
        // e "anexa" os dados da tabela 'professionals'
        $professionals = User::where('role', 'professional')
                            ->with('professional') // Usa a relação que criamos no User.php
                            ->get();

        return response()->json($professionals);
    }
    /**
     * Aprova um profissional (muda status de 'pending' para 'active').
     */
    public function approveProfessional($id)
    {
        // Busca o usuário pelo ID
        $user = User::findOrFail($id);

        // Verifica se é mesmo um profissional
        if ($user->role !== 'professional') {
            return response()->json(['error' => 'Apenas profissionais podem ser aprovados.'], 400);
        }

        // Atualiza o status
        $user->status = 'active';
        $user->save();

        return response()->json(['message' => 'Profissional aprovado com sucesso!', 'user' => $user]);
    }

    public function show($id)
    {
        // Busca o usuário e traz junto os dados do perfil 'professional'
        $user = User::with('professional')->findOrFail($id);

        // Proteção extra: garante que não estamos vendo um admin ou paciente por engano
        if ($user->role !== 'professional') {
            return response()->json(['error' => 'Usuário inválido.'], 404);
        }

        return response()->json($user);
    }
}
