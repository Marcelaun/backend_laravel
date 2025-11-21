<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; // <-- Importante
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // <-- Usamos Request normal, não o EmailVerificationRequest

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request, $id): RedirectResponse
    {
        // 1. Busca o usuário pelo ID que está na URL
        $user = User::findOrFail($id);

        // 2. Verifica se a assinatura do link é válida (Segurança)
        // Se o link foi alterado ou expirou, negamos o acesso.
        if (! $request->hasValidSignature()) {
            // Redireciona para o login com erro (opcional)
            return redirect(config('app.frontend_url') . '/login?error=invalid_signature');
        }

        // 3. Verifica se já foi verificado antes
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url').'/dashboard?verified=1'
            );
        }

        // 4. Marca como verificado
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // 5. Redireciona para o Dashboard
        return redirect()->intended(
            config('app.frontend_url').'/dashboard?verified=1'
        );
    }
}
