<?php

declare(strict_types=1);

namespace App\Middleware;

use Wibiesana\Padi\Core\Request;
use Wibiesana\Padi\Core\Env;
use Wibiesana\Padi\Core\Cache;

class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSize;

    public function __construct()
    {
        $this->maxRequests = (int)Env::get('RATE_LIMIT_MAX', 60);
        $this->windowSize  = (int)Env::get('RATE_LIMIT_WINDOW', 60);
    }

    public function handle(Request $request): void
    {
        $ip  = $request->ip();
        $key = 'rate_limit_' . md5($ip);

        $now         = time();
        $windowStart = $now - $this->windowSize;

        // Get request history from Cache (supports Redis or File, no race condition)
        $requests = Cache::get($key, []);

        // Filter requests within sliding window
        $requests = array_values(array_filter($requests, fn($timestamp) => $timestamp > $windowStart));

        if (count($requests) >= $this->maxRequests) {
            throw new \Exception('Too many requests. Please try again later.', 429);
        }

        // Add current request timestamp and persist
        $requests[] = $now;
        Cache::set($key, $requests, $this->windowSize + 10);
    }
}
