<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        //cek apakah user sudah login, jika belum maka akan di redirect ke halaman login
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        //cek apakah role user sesuai dengan role yang diizinkan, jika tidak maka akan di redirect ke halaman 403
        $userRole = Auth::user()->role;

        if (empty($roles) || ! in_array($userRole, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}