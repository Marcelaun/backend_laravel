<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Session\Middleware\StartSession;


// --- ROTAS PÚBLICAS (guest) ---
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

    Route::post('/login-token', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciais inválidas'], 401);
    }

    $user = Auth::user();

    // Remove tokens antigos do usuário
    $user->tokens()->delete();

    // Cria novo token
    $token = $user->createToken('insomnia-api')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});

// --- ROTAS PROTEGIDAS (auth:sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Pacientes
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients', [PatientController::class, 'index']);

    // Análises
    Route::post('/analyses', [AnalysisController::class, 'store']);
    Route::get('/analyses', [AnalysisController::class, 'index']);
    Route::get('/analyses/{analysis}', [AnalysisController::class, 'show']);

    // PDF
    Route::get('/laudo/{analysis}/pdf', [AnalysisController::class, 'downloadLaudo']);
});

// --- ROTAS SOMENTE ADMIN ---
// Aqui aplicamos:
//  ✔ auth:sanctum
//  ✔ EnsureFrontendRequestsAreStateful
//  ✔ StartSession
//  ✔ CheckAdminRole
Route::middleware([
    'auth:sanctum',

    App\Http\Middleware\CheckAdminRole::class,
])->group(function () {

    Route::get('/admin/professionals', [AdminController::class, 'getProfessionals']);
});
