<?php

namespace App\Services;

use App\Models\Usuario;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;

class JwtTokenService
{
    public function issueToken(Usuario $usuario): string
    {
        $issuedAt = Carbon::now()->timestamp;
        $ttl = config('jwt.ttl');

        $payload = [
            'sub' => $usuario->id,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $ttl,
        ];

        return JWT::encode($payload, $this->secret(), config('jwt.algo'));
    }

    /**
     * @return array{sub: int, iat: int, exp: int}
     */
    public function decodeToken(string $token): array
    {
        $decoded = JWT::decode($token, new Key($this->secret(), config('jwt.algo')));

        return [
            'sub' => (int) $decoded->sub,
            'iat' => (int) $decoded->iat,
            'exp' => (int) $decoded->exp,
        ];
    }

    private function secret(): string
    {
        $secret = (string) config('jwt.secret', '');

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);

            return $decoded !== false ? $decoded : '';
        }

        return $secret;
    }
}
