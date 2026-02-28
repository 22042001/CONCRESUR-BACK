<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use App\Services\JwtTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly JwtTokenService $jwtTokenService)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::query()
            ->where('email', (string) $request->string('email'))
            ->where('activo', true)
            ->first();

        if (! $usuario || ! Hash::check((string) $request->string('password'), $usuario->password_hash)) {
            return response()->json([
                'message' => 'Credenciales invalidas.',
            ], 401);
        }

        return response()->json([
            'access_token' => $this->jwtTokenService->issueToken($usuario),
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl'),
            'message' => 'Login exitoso.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return $this->success([
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'rol' => $usuario->rol?->nombre,
            'permisos' => $usuario->rol
                ? $usuario->rol->permisos()->orderBy('clave')->pluck('clave')->values()->all()
                : [],
        ], 'Perfil obtenido.');
    }
}
