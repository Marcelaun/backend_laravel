<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Checa se o usuário está logado E se a 'role' dele é 'admin'
    if (Auth::check() && Auth::user()->role === 'admin') {
        // 2. Se sim, permite que a requisição continue
        return $next($request);
    }

    // 3. Se não, bloqueia com um erro "Proibido"
    return response()->json(['error' => 'Acesso não autorizado. Apenas administradores.'], 403);
    }
}
