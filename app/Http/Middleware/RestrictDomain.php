<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictDomain
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $email = Auth::user()->email;

            if (!str_ends_with($email, '@tuempresa.com')) {
                abort(403, 'Solo usuarios de la empresa');
            }
        }

        return $next($request);
    }
}
