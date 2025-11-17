<?php

namespace App\Http\Controllers;

use App\Models\Patient; // Importa o "molde" do Paciente que criamos
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importa o helper de Autenticação

class PatientController extends Controller
{
    /**
     * Armazena um novo paciente no banco de dados.
     * (Corresponde ao formulário 'Cadastrar Paciente' do React)
     */
    public function store(Request $request)
    {
        // 1. Validação: Garante que os dados do React estão corretos
        //    Isso é uma proteção do backend (RF02)
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14|unique:patients', // CPF é único na tabela 'patients'
            'birth_date' => 'required|date',
            'sexo' => 'required|string',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:patients', // Email também é único

            // Dados Clínicos
            'tipo_diabetes' => 'required|string',
            'usa_insulina' => 'required|string',
            'diagnosis_time' => 'nullable|string',
            'current_medication' => 'nullable|string',
            'comorbidities' => 'nullable|string',
        ]);

        // 2. Pega o ID do Profissional que está logado
        $professionalId = Auth::id();

        // 3. Adiciona o ID do profissional aos dados validados
        $patientData = $validatedData;
        $patientData['created_by_professional_id'] = $professionalId;

        // 4. Cria o paciente no banco de dados
        //    Isso usa o Model 'Patient' para criar uma nova linha
        //    na tabela 'patients' do seu Supabase.
        $patient = Patient::create($patientData);

        // 5. Retorna uma resposta de Sucesso (201 - Created)
        return response()->json([
            'message' => 'Paciente cadastrado com sucesso!',
            'patient' => $patient // Devolve o paciente que acabou de ser criado
        ], 201);
    }
    // * Lista todos os pacientes que pertencem
    //  * ao profissional de saúde atualmente logado.
    //  */
    public function index()
    {
        // 1. Pega o ID do profissional que está fazendo a requisição
        $professionalId = Auth::id();

        // 2. Busca no banco de dados (Supabase) todos os pacientes
        //    onde o 'created_by_professional_id' bate com o ID do profissional logado.
        //    Também ordena os resultados pelo mais recente ('latest()').
        $patients = Patient::where('created_by_professional_id', $professionalId)
                            ->latest() // Opcional: ordena do mais novo para o mais antigo
                            ->get();

        // 3. Retorna a lista de pacientes como um JSON
        return response()->json($patients);
    }
}
