<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException; // Tambahkan ini
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Matikan CSRF untuk API (seperti yang kita bahas sebelumnya)
        $middleware->validateCsrfTokens(except: [
            'api/*', 'login', 'register', 'logout'
        ]);

        $middleware->trustProxies(at: '*');

        // --- TAMBAHAN PENTING ---
        // Ini memaksa Laravel: "Kalau user belum login, JANGAN redirect ke route('login')."
        $middleware->redirectGuestsTo(function (Request $request) {
            // Kalau request mau akses API, return null (nanti jadi 401 JSON)
            if ($request->is('api/*')) {
                return null;
            }
            // Default behavior (kalau ada web frontend)
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Opsional: Tangkap AuthenticationException biar pesannya rapi
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 'error'
                ], 401);
            }
        });
    })->create();
