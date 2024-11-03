<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Log untuk debug
        \Log::info('Auth check:', [
            'expectsJson' => $request->expectsJson(),
            'hasToken' => $request->bearerToken() ? true : false,
        ]);

        // Jika permintaan mengharapkan JSON, jangan arahkan; sebaliknya, kembalikan route login
        if (!$request->expectsJson()) {
            return route('login');
        }

        return null;
    }
    protected function unauthenticated($request, array $guards)
    {
        // Tambahkan log tambahan jika diperlukan
        \Log::warning('Unauthenticated access attempt.', [
            'guards' => $guards,
            'url' => $request->url(),
        ]);

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
