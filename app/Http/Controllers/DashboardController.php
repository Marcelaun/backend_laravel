<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use App\Models\Analysis;
use Carbon\Carbon; // Para lidar com datas (hoje)

class DashboardController extends Controller
{
    public function index()
    {
        $professionalId = Auth::id();

        // 1. Total de Pacientes do médico
        $totalPatients = Patient::where('created_by_professional_id', $professionalId)->count();

        // 2. Análises Feitas Hoje
        $todayAnalyses = Analysis::where('professional_id', $professionalId)
                                 ->whereDate('created_at', Carbon::today())
                                 ->count();

        // 3. Casos Urgentes (RD Severa ou Proliferativa)
        $urgentCases = Analysis::where('professional_id', $professionalId)
                               ->whereIn('ai_summary_diagnosis', ['RD Severa', 'RD Proliferativa'])
                               ->count();

        // 4. Estatísticas para as Barras de Progresso (Total de casos por tipo)
        $stats = [
            'Normal' => Analysis::where('professional_id', $professionalId)->where('ai_summary_diagnosis', 'Normal')->count(),
            'RD Leve' => Analysis::where('professional_id', $professionalId)->where('ai_summary_diagnosis', 'RD Leve')->count(),
            'RD Moderada' => Analysis::where('professional_id', $professionalId)->where('ai_summary_diagnosis', 'RD Moderada')->count(),
            'RD Severa' => Analysis::where('professional_id', $professionalId)->where('ai_summary_diagnosis', 'RD Severa')->count(),
            'RD Proliferativa' => Analysis::where('professional_id', $professionalId)->where('ai_summary_diagnosis', 'RD Proliferativa')->count(),
        ];

        // 5. Análises Recentes (As últimas 3)
        $recentAnalyses = Analysis::with('patient') // Carrega o nome do paciente junto
                                  ->where('professional_id', $professionalId)
                                  ->latest()
                                  ->take(3)
                                  ->get()
                                  ->map(function ($analysis) {
                                      return [
                                          'id' => $analysis->id,
                                          'patient_name' => $analysis->patient->nome,
                                          'date' => $analysis->created_at->format('d/m/Y'),
                                          'result' => $analysis->ai_summary_diagnosis,
                                          'status' => $analysis->status // 'pendente' ou 'concluido'
                                      ];
                                  });

        return response()->json([
            'total_patients' => $totalPatients,
            'today_analyses' => $todayAnalyses,
            'urgent_cases' => $urgentCases,
            'stats' => $stats,
            'recent_analyses' => $recentAnalyses
        ]);
    }
}
