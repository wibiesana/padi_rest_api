<?php

declare(strict_types=1);

namespace App\Middleware;

use Wibiesana\Padi\Core\Request;
use Wibiesana\Padi\Core\Auth;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new \Exception('Unauthorized - No token provided', 401);
        }

        $decoded = Auth::verifyToken($token);

        if (!$decoded) {
            throw new \Exception('Unauthorized - Invalid or expired token', 401);
        }

        // Attach user info to request for later use
        $request->user = $decoded;
    }
}
