<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if ($header && str_starts_with($header, 'Bearer ')) {
            $plainToken = substr($header, 7);
            $token = ApiToken::with('user')->get()->first(function ($apiToken) use ($plainToken) {
                return Hash::check($plainToken, $apiToken->token);
            });

            if ($token && ! $token->isExpired() && $token->user) {
                $token->markAsUsed();
                Auth::login($token->user);
                $request->attributes->set('api_token', $token);
            }
        }

        if (! Auth::check()) {
            return response()->json(['message' => __('Unauthenticated.')], 401);
        }

        return $next($request);
    }
}
