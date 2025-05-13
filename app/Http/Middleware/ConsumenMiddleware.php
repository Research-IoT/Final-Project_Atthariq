<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Consumen;

class ConsumenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->bearerToken();

        if (!$authorization) {
            return response()->json(['message' => 'Authorization Bearer token is required'], 401);
        }

        $user = Consumen::where('token', $authorization)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}
