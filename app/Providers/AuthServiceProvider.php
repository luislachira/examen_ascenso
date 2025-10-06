<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        // --- Configuración de Laravel Passport ---

        // 1. Registra las rutas necesarias para que Passport funcione
        // (ej. /oauth/token, /oauth/authorize, etc.)
        // Esta es la línea más importante.
        Passport::routes();

        // 2. (Opcional pero recomendado) Define la vida útil de los tokens.
        // Esto mejora la seguridad al hacer que los tokens expiren.

        // El token de acceso principal. Un buen valor es entre 1 y 24 horas.
        Passport::tokensExpireIn(Carbon::now()->addHours(8));

        // El refresh token permite obtener un nuevo access token sin volver a pedir la contraseña.
        // Debe tener una vida útil más larga.
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));

        // Para tokens de acceso personal (no los usaremos en el flujo de contraseña, pero es bueno tenerlo).
        Passport::personalAccessTokensExpireIn(Carbon::now()->addMonths(6));

        // 3. (Opcional) Aquí podrías definir "scopes" o permisos si tu API los necesitara.
        // Por ejemplo, para dar permisos de solo lectura o de escritura.
        /*
        Passport::tokensCan([
            'read-exam' => 'Ver información de exámenes',
            'submit-exam' => 'Enviar resultados de un examen',
            'manage-users' => 'Administrar usuarios (solo para admins)',
        ]);
        */
    }
}
