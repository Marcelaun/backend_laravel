<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // ADICIONE ESTA LINHA:
        // Isso liga o middleware de "StartSession" para todas as rotas de API,
        // o que corrige o erro "Session store not set on request"
        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/login-token', // <--- A rota que estamos usando para logar
            'api/*',           // Opcional: Se quiser liberar a API toda (mais radical, mas resolve)
        ]);

        // (Seu middleware 'admin' que criamos antes pode já estar aqui,
        // ou você pode adicioná-lo depois, não tem problema)
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdminRole::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
