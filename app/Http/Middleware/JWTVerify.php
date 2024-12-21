<?php

namespace App\Http\Middleware;

use App\Helpers\PublicHelper;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class JWTVerify
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
        $publicHelper = new PublicHelper();

        try {
            $token = $request->bearerToken();
            if ($token) {
                $tokenHash = hash('sha256', $token);
                $isBlacklisted = Cache::has('blacklist_' . $tokenHash);
                
                Log::info('Token check', [
                    'token_hash' => $tokenHash,
                    'is_blacklisted' => $isBlacklisted  ]);
                if ($isBlacklisted) {
                    throw new AuthenticationException('Token has been blacklisted');
                }
            }
            
            $decodedToken = $publicHelper->getAndDecodeJWT();
            
            if (isset($decodedToken->exp) && time() >= $decodedToken->exp) {
                throw new AuthenticationException('Token has expired');
            }
                
            return $next($request);
        } catch (ExpiredException $e) {
            return response()->json([
                'error' => 'Token has expired',
                'status' => 401
            ], 401);
        } catch (AuthenticationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 401
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'status' => 401
            ], 401);
        }
    }
}
