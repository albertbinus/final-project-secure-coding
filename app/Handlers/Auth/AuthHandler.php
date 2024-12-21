<?php

namespace App\Handlers\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
class AuthHandler
{
    /**
     * Handles operations related to admin authentication
     */

    // generate token
    public function generateToken($user)
    {
        $secretKey  = env('JWT_KEY');
        $tokenId    = base64_encode(random_bytes(16));
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+60 minutes')->getTimestamp();
        $serverName = "localhost";

        // Create the token as an array
        $data = [
            'iat'  => $issuedAt->getTimestamp(),
            'jti'  => $tokenId,
            'iss'  => $serverName,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire,
            'data' => [
                'userID' => $user->id,
                'role' => $user->role
            ]
        ];

        // Encode the array to a JWT string.
        return JWT::encode(
            $data,
            $secretKey,
            'HS512'
        );
    }

    public function blacklistToken($token)
    {
        $decoded = JWT::decode($token, new Key(env('JWT_KEY'), 'HS512'));
        $expiration = $decoded->exp;
        $tokenHash = hash('sha256', $token);
        
        $success = Cache::put('blacklist_' . $tokenHash, true, $expiration - time());
        
        \Log::info('Token blacklisted', [
            'token_hash' => $tokenHash,
            'expiration' => $expiration,
            'cache_success' => $success
        ]);
    }
}
