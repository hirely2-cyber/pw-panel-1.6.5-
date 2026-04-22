<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePanel
{
    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        if (! Auth::guard('panel')->check()) {
            return redirect()->guest(route('panel.login'));
        }

        $user = Auth::guard('panel')->user();

        if (! $user->is_active) {
            Auth::guard('panel')->logout();
            return redirect()->route('panel.login')->withErrors([
                'username' => 'Akun kamu dinonaktifkan.',
            ]);
        }

        if ($ability && ! $user->can($ability)) {
            abort(403, "Missing permission: {$ability}");
        }

        return $next($request);
    }
}
