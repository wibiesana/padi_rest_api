<?php

declare(strict_types=1);

namespace App\Middleware;

use Wibiesana\Padi\Core\Request;
use Wibiesana\Padi\Core\Response;

class CorsMiddleware
{
    public function handle(Request $request): void
    {
        // CORS headers are now primarily handled in public/index.php
        // This middleware is kept for backward compatibility or custom logic.

        if ($request->method() === 'OPTIONS') {
            // Handled in index.php, but if reached here, send empty 200
            $response = new Response();
            $response->status(200)->text('');
            // terminate() in Response will handle worker mode return
        }
    }
}
