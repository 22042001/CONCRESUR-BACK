<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Services\JwtTokenService;
use Closure;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateWithJwt
{
    public function __construct(private readonly JwtTokenService $jwtTokenService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorized('Token no proporcionado.');
        }

        try {
            $payload = $this->jwtTokenService->decodeToken($token);
        } catch (ExpiredException) {
            return $this->unauthorized('Token expirado.');
        } catch (Throwable) {
            return $this->unauthorized('Token invalido.');
        }

        $usuario = Usuario::query()
            ->where('id', $payload['sub'])
            ->where('activo', true)
            ->first();

        if (! $usuario) {
            return $this->unauthorized('Usuario no encontrado.');
        }

        Auth::setUser($usuario);
        $request->setUserResolver(static fn (): Usuario => $usuario);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => null,
            'errors' => [],
        ], 401);
    }
}
