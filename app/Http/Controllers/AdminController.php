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
}
