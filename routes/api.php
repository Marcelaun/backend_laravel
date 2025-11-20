<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Http\Request;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Session\Middleware\StartSession;


// --- ROTAS PÚBLICAS (guest) ---
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/patient/login', [PatientController::class, 'login']);

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');


Route::post('/patient/analysis', [AnalysisController::class, 'showForPatient']);
Route::post('/patient/history', [AnalysisController::class, 'historyForPatient']);
Route::post('/patient/pdf', [AnalysisController::class, 'downloadLaudoForPatient']);

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

   Route::post('/login-token', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciais inválidas'], 401);
    }

    $user = Auth::user();

    // Remove tokens antigos (Boa prática de segurança)
    $user->tokens()->delete();

    // Cria novo token
    $token = $user->createToken('auth_token')->plainTextToken;

    // *** AQUI ESTÁ A MUDANÇA IMPORTANTE ***
    // Carrega os dados extras (CRM, CPF, etc.) antes de enviar para o React
    $user->load('professional');

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});



// --- ROTAS PROTEGIDAS (auth:sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user()->load('professional');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

        Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'updatePassword']);

    // Pacientes
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::put('/patients/{id}', [PatientController::class, 'update']);

    // Análises
    Route::post('/analyses', [AnalysisController::class, 'store']);
    Route::get('/analyses', [AnalysisController::class, 'index']);
    Route::get('/analyses/{analysis}', [AnalysisController::class, 'show']);
    Route::put('/analyses/{analysis}', [AnalysisController::class, 'update']);
    Route::delete('/analyses/{id}', [AnalysisController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // Dentro do grupo auth:sanctum
    Route::get('/patients/{id}/analyses', [PatientController::class, 'analyses']);

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
    Route::get('/admin/users/{id}', [AdminController::class, 'show']);
    Route::get('/admin/professionals', [AdminController::class, 'getProfessionals']);
    Route::put('/admin/users/{id}/approve', [AdminController::class, 'approveProfessional']);
});
