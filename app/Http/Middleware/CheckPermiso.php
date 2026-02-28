<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    public function handle(Request $request, Closure $next, string $permisoClave): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = $request->user();

        if (! $usuario || ! $usuario->rol) {
            return $this->forbidden('No autorizado para esta accion.');
        }

        $hasPermiso = $usuario->rol
            ->permisos()
            ->where('clave', $permisoClave)
            ->exists();

        if (! $hasPermiso) {
            return $this->forbidden('No cuenta con el permiso requerido.');
        }

        return $next($request);
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => null,
            'errors' => [],
        ], 403);
    }
}
