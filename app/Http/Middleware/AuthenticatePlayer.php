<?php

namespace App\Http\Middleware;

use App\Domain\Player\Services\PlayerAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePlayer
{
    public function __construct(
        private readonly PlayerAuthService $authService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?? $request->header('X-Player-Token')
            ?? $request->query('player_token');

        if (! $token) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $player = $this->authService->validateToken((string) $token);

        if (! $player) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $request->attributes->set('player', $player);
        $request->setUserResolver(fn () => $player);

        return $next($request);
    }
}
