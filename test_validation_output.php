<?php
require_once 'vendor/autoload.php';

use Core\Env;

// Load environment  
Env::load(__DIR__ . '/.env');

// Start output buffering untuk capture response
ob_start();

// Simulate POST request with empty data
$_POST = [
    'title' => '',
    'subject' => '',
    'question_text' => '',
    'correct_answer' => ''
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

echo "=== TESTING WITH OUTPUT BUFFER ===\n";

try {
    $request = new Core\Request();
    $controller = new App\Controllers\QuestionbankController($request);

    // Capture any output from validation
    $controller->store();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// Get captured output
$output = ob_get_contents();
ob_end_clean();

echo "Captured output:\n";
echo $output;

// Also test with valid data
echo "\n\n=== TESTING WITH VALID DATA ===\n";

$_POST = [
    'title' => 'Test Question',
    'subject' => 'Mathematics',
    'question_text' => 'What is 2+2?',
    'correct_answer' => '4'
];

ob_start();

try {
    $request = new Core\Request();
    $controller = new App\Controllers\QuestionbankController($request);
    $controller->store();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

$validOutput = ob_get_contents();
ob_end_clean();

echo "Valid data output:\n";
echo $validOutput;
