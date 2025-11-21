<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail; // <-- Importante
use Illuminate\Notifications\Messages\MailMessage; // <-- Importante
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ============================================================
        // 1. PERSONALIZAÇÃO DO E-MAIL DE RECUPERAÇÃO DE SENHA
        // ============================================================
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {

            // Monta o link para o seu FRONTEND (React)
            // Ex: http://localhost:5173/password-reset/TOKEN?email=...
            $url = config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";

            return (new MailMessage)
                ->subject('Redefinição de Senha - VisusAI') // Assunto do E-mail
                ->greeting('Olá!') // Saudação
                ->line('Você está recebendo este e-mail porque recebemos um pedido de redefinição de senha para sua conta.')
                ->action('Redefinir Minha Senha', $url) // Botão e Link
                ->line('Este link de redefinição de senha expirará em 60 minutos.')
                ->line('Se você não solicitou a redefinição de senha, nenhuma ação é necessária.')
                ->salutation('Atenciosamente, Equipe VisusAI');
        });


        // ============================================================
        // 2. PERSONALIZAÇÃO DO E-MAIL DE VERIFICAÇÃO (Que já fizemos)
        // ============================================================
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifique seu endereço de e-mail - VisusAI')
                ->greeting('Olá, ' . $notifiable->name . '!')
                ->line('Obrigado por se cadastrar na plataforma VisusAI.')
                ->line('Por favor, clique no botão abaixo para confirmar que este e-mail é seu.')
                ->action('Verificar E-mail Agora', $url)
                ->line('Se você não criou uma conta, nenhuma ação é necessária.')
                ->salutation('Atenciosamente, Equipe VisusAI');
        });
    }
}
