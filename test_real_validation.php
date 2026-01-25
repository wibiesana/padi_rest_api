<?php
require_once 'vendor/autoload.php';

use Core\Env;

// Load environment
Env::load(__DIR__ . '/.env');

// Simulate POST request with empty data
$_POST = [
    'title' => '',
    'subject' => '',
    'question_text' => '',
    'correct_answer' => ''
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

echo "=== TESTING QUESTIONBANK CREATE WITH EMPTY DATA ===\n";
echo "POST data: " . json_encode($_POST) . "\n\n";

// Try to create QuestionBank
try {
    $request = new Core\Request();
    $controller = new App\Controllers\QuestionbankController($request);

    echo "Calling store() method...\n";
    $controller->store();
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
}
