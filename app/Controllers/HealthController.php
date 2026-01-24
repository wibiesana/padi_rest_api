<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Env;

class HealthController extends Controller
{
    /**
     * Display API information
     */
    public function index(): void
    {
        $response = new Response();
        $response->json([
            'success' => true,
            'aplikasi' => Env::get('APP_NAME', 'Padi REST API'),
            'status' => 'Up and running',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health(): void
    {
        $response = new Response();
        $response->json([
            'success' => true,
            'environment' => Env::get('APP_ENV', 'production'),
            'debug' => Env::get('APP_DEBUG', 'false') === 'true',
            'message' => Env::get('APP_NAME', 'Padi REST API') . ' is running',
            'version' => Env::get('APP_VERSION', '2.0.0'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
