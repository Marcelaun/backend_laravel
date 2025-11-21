<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Se já verificou, retorna um JSON avisando, em vez de redirecionar
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => 'already-verified']);
        }

        // 2. Envia o e-mail
        $request->user()->sendEmailVerificationNotification();

        // 3. Retorna sucesso
        return response()->json(['status' => 'verification-link-sent']);
    }
}
