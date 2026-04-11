<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\File;
use Wibiesana\Padi\Core\Env;
use Exception;

class FileController extends Controller
{
    /**
     * Upload an image from CKEditor
     * POST /file/upload-question-image
     */
    public function uploadQuestionImage()
    {
        // Padi framework handles files via $_FILES
        if (!isset($_FILES['file'])) {
            throw new Exception("No file uploaded", 400);
        }

        try {
            // Upload to 'question' directory (private)
            $path = File::upload($_FILES['file'], 'question', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

            // Generate URL that points to our proxy route
            $appUrl = Env::get('APP_URL', 'http://localhost:8085');
            $url = rtrim($appUrl, '/') . '/file/show/' . $path;

            return [
                'url' => $url,
                'path' => $path
            ];
        } catch (Exception $e) {
            throw new Exception("Upload failed: " . $e->getMessage(), 500);
        }
    }

    /**
     * Serve a file from private storage
     * GET /file/show/{path}
     */
    public function serve()
    {
        $path = $this->request->param('path');

        $fullPath = dirname(dirname(__DIR__)) . '/uploads/' . ltrim($path, '/');

        if (!file_exists($fullPath) || is_dir($fullPath)) {
            header("HTTP/1.0 404 Not Found");
            exit('File not found');
        }

        // Try to get mime type, fallback to common types based on extension
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
                'svg' => 'image/svg+xml'
            ];
            $mimeType = $map[$ext] ?? $mimeType;
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=31536000');

        readfile($fullPath);
        exit;
    }
}
