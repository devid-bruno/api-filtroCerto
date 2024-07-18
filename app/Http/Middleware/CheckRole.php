<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Não autorizado.'], 401);
        }

        $user = Auth::user();

        if ($user->role_id != 2) {
            return response()->json(['message' => 'Permissão negada.'], 403);
        }

        return $next($request);
    }
}

