<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;

class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSize;
    private string $cacheDir;

    public function __construct()
    {
        $this->maxRequests = (int)($_ENV['RATE_LIMIT_MAX'] ?? 60);
        $this->windowSize = (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60);
        $this->cacheDir = dirname(__DIR__, 2) . '/storage/cache/ratelimit/';
    }

    public function handle(Request $request): void
    {
        $ip = $request->ip();
        $key = 'rate_limit_' . md5($ip);
        $cacheFile = $this->cacheDir . $key;

        // Create cache directory if not exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $now = time();
        $windowStart = $now - $this->windowSize;

        // Get request history
        $requests = [];
        if (file_exists($cacheFile)) {
            $requests = json_decode(file_get_contents($cacheFile), true) ?? [];
        }

        // Filter requests within time window
        $requests = array_filter($requests, fn($timestamp) => $timestamp > $windowStart);

        if (count($requests) >= $this->maxRequests) {
            throw new \Exception('Too many requests. Please try again later.', 429);
        }

        // Add current request
        $requests[] = $now;
        file_put_contents($cacheFile, json_encode($requests));
    }
}
