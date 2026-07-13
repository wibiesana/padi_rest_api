<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Realtime;

/**
 * Example Realtime Controller
 * 
 * Showcases how to broadcast real-time updates and events using FrankenPHP Mercure:
 * 1. Broadcasting to a public channel (e.g., chat room)
 * 2. Broadcasting private messages (e.g., specific user alerts)
 * 3. Targeting specific subscriber authorization groups
 * 4. Fetching dynamic SSE subscription handshake tokens
 */
class ExampleRealtimeController extends Controller
{
    /**
     * Example 1: Broadcast to a public channel
     * POST /api/realtime/chat
     */
    public function broadcastChatMessage()
    {
        $validated = $this->validate([
            'message' => 'required|string|max:1000',
            'username' => 'string|max:50'
        ]);

        $payload = [
            'username' => $validated['username'] ?? 'Anonymous',
            'message' => $validated['message'],
            'sent_at' => date('Y-m-d H:i:s')
        ];

        // Publish to public 'public-chat' topic
        $success = Realtime::publish('public-chat', $payload);

        return [
            'success' => $success,
            'topic' => 'public-chat',
            'payload' => $payload,
            'message' => $success ? 'Message broadcasted successfully' : 'Broadcasting failed (Mercure disabled or offline)'
        ];
    }

    /**
     * Example 2: Broadcast private notifications to a specific user
     * POST /api/realtime/notify
     */
    public function sendPrivateNotification()
    {
        $validated = $this->validate([
            'user_id' => 'required|integer',
            'message' => 'required|string|max:500'
        ]);

        $payload = [
            'title' => 'Personal Notification',
            'message' => $validated['message'],
            'timestamp' => time()
        ];

        $topic = 'user-notifications-' . $validated['user_id'];

        // Publish as a private Mercure topic
        $success = Realtime::publish($topic, $payload, true);

        return [
            'success' => $success,
            'topic' => $topic,
            'payload' => $payload,
            'message' => $success ? 'Private notification pushed successfully' : 'Failed to push private notification'
        ];
    }

    /**
     * Example 3: Send system alert targeting specific subscriber groups
     * POST /api/realtime/alert
     */
    public function sendSystemAlert()
    {
        $this->requireRole('admin'); // Only administrators can broadcast system alerts

        $validated = $this->validate([
            'message' => 'required|string|max:500'
        ]);

        $payload = [
            'alert_level' => 'WARNING',
            'message' => $validated['message'],
            'sent_at' => date('Y-m-d H:i:s')
        ];

        // Send alert to users authorized with either 'admin' or 'moderators' target
        $success = Realtime::publish('system-alerts', $payload, true, ['admin', 'moderators']);

        return [
            'success' => $success,
            'topic' => 'system-alerts',
            'payload' => $payload
        ];
    }

    /**
     * Example 4: Request dynamic subscriber JWT token for custom topics
     * POST /api/realtime/token
     */
    public function getCustomSubscribeToken()
    {
        if ($this->request->user === null) {
            throw new \Exception('Authentication required', 401);
        }

        $validated = $this->validate([
            'topics' => 'required|array'
        ]);

        // Validate user is authorized to subscribe to requested topics
        foreach ($validated['topics'] as $topic) {
            // E.g., prevent subscribing to other users' topics
            if (str_starts_with($topic, 'user-notifications-') && $topic !== 'user-notifications-' . $this->request->user->user_id) {
                throw new \Exception("Unauthorized to subscribe to topic: {$topic}", 403);
            }
        }

        // Generate customized JWT token for requested topics
        $token = Realtime::generateSubscriberJwt($validated['topics']);

        return [
            'token' => $token,
            'hub_url' => Realtime::getHubUrl(),
            'topics' => $validated['topics']
        ];
    }
}
