<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PublicHelper;
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {

        $publicHelper = new PublicHelper();
        $token = $publicHelper->getAndDecodeJWT();
        
        if (!in_array($token->data->role, $roles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
         return $next($request);
    }
}

