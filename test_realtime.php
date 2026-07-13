<?php

/**
 * Padi REST API Framework - Real-time Mercure Hub Demonstration Script.
 * 
 * Run this script via PHP CLI:
 *   php test_realtime.php
 */

require_once __DIR__ . '/public/index.php'; // Bootstraps composer, env, etc.

use Wibiesana\Padi\Core\Realtime;
use Wibiesana\Padi\Core\Env;

// Clear output buffers to print immediately
ob_end_clean();

echo "\n=======================================================\n";
echo "  Padi REST API - Mercure Realtime Demo & Tester\n";
echo "=======================================================\n\n";

if (!Env::get('MERCURE_ENABLED', false)) {
    echo "❌ ERROR: Realtime is currently disabled in your .env!\n";
    echo "Please set MERCURE_ENABLED=true in your .env to run this demo.\n\n";
    exit(1);
}

$topic = "user-notifications-1";
$messagePayload = [
    'title' => 'System Notification',
    'message' => 'Real-time update triggered successfully at ' . date('H:i:s'),
    'timestamp' => time()
];

echo "1. Client Connection Code Snippet (HTML/JS):\n";
echo "-------------------------------------------------------\n";
$hubUrl = Realtime::getHubUrl();
$token = Realtime::generateSubscriberJwt([$topic]);

echo "Copy this HTML code into a file (e.g. index.html) and open it in your browser:\n\n";
echo "```html\n";
echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Padi Realtime Client</title>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <h2>Real-time Notifications</h2>\n";
echo "    <div id=\"events\">Listening for events on topic '{$topic}'...</div>\n";
echo "    <script>\n";
echo "        // Connect to Mercure SSE Hub\n";
echo "        const hubUrl = new URL('{$hubUrl}');\n";
echo "        hubUrl.searchParams.append('topic', '{$topic}');\n";
echo "        \n";
echo "        // Create EventSource connection\n";
echo "        const eventSource = new EventSource(hubUrl);\n";
echo "        \n";
echo "        eventSource.onmessage = event => {\n";
echo "            const data = JSON.parse(event.data);\n";
echo "            console.log('Received:', data);\n";
echo "            const el = document.getElementById('events');\n";
echo "            el.innerHTML += '<p><strong>' + data.title + ':</strong> ' + data.message + '</p>';\n";
echo "        };\n";
echo "    </script>\n";
echo "</body>\n";
echo "</html>\n";
echo "```\n\n";

echo "2. Publishing test event:\n";
echo "-------------------------------------------------------\n";
echo "Publishing to topic '{$topic}'...\n";
echo "Payload: " . json_encode($messagePayload, JSON_PRETTY_PRINT) . "\n\n";

$status = Realtime::publish($topic, $messagePayload);

if ($status) {
    echo "✅ SUCCESS: Message published to Mercure Hub successfully!\n";
} else {
    echo "❌ FAILED: Failed to publish message. Check logs or verify FrankenPHP Mercure Hub is running.\n";
}
echo "\n=======================================================\n\n";
