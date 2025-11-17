<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenExpiration
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
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            
            if ($token) {
                // Check if token is older than 24 hours
                if ($token->created_at->lt(now()->subHours(24))) {
                    // Delete expired token
                    $token->delete();
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token expired. Please login again.',
                        'error_code' => 'TOKEN_EXPIRED'
                    ], 401);
                }
            }
        }

        return $next($request);
    }
}