<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Exception;

class UtilsController extends Controller
{
    /**
     * Render file content for preview (PDF/Image)
     * POST /utils/render-content
     */
    public function renderContent()
    {
        $path = $this->request->input('path');
        
        if (empty($path)) {
            throw new Exception("Path is required", 400);
        }

        // Security check: prevent directory traversal
        if (strpos($path, '..') !== false) {
            throw new Exception("Invalid path", 403);
        }

        // Resolve absolute path (relative to backend root)
        $fullPath = dirname(dirname(__DIR__)) . '/uploads/' . ltrim($path, '/');

        if (!file_exists($fullPath) || is_dir($fullPath)) {
            header("HTTP/1.0 404 Not Found");
            exit('File not found');
        }

        // Detect mime type
        $mimeType = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($fullPath);
        } else {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $map = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'pdf' => 'application/pdf'
            ];
            $mimeType = $map[$ext] ?? $mimeType;
        }

        // Serve file content
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($fullPath));
        header('Access-Control-Allow-Origin: *'); // Allow CORS if needed
        header('Cache-Control: public, max-age=3600');

        readfile($fullPath);
        exit;
    }
}
