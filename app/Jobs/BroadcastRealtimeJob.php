<?php

namespace App\Jobs;

use Wibiesana\Padi\Core\Logger;
use Wibiesana\Padi\Core\Realtime;

/**
 * Background Job to broadcast Real-time SSE updates asynchronously.
 * 
 * Recommended for high-load production setups to prevent blocking main HTTP threads.
 */
class BroadcastRealtimeJob
{
    /**
     * Handle the job process.
     * The Queue worker will call this method with the provided data.
     */
    public function handle(array $data): void
    {
        $topic = $data['topic'] ?? null;
        $payload = $data['data'] ?? [];
        $private = $data['private'] ?? false;
        $targets = $data['targets'] ?? [];

        if (empty($topic)) {
            Logger::error("BroadcastRealtimeJob failed: Topic is missing");
            return;
        }

        Logger::info("BroadcastRealtimeJob started for topic: " . $topic);

        $success = Realtime::publish($topic, $payload, $private, $targets);

        if ($success) {
            Logger::info("BroadcastRealtimeJob completed for topic: " . $topic);
        } else {
            Logger::error("BroadcastRealtimeJob failed to publish topic: " . $topic);
        }
    }
}
