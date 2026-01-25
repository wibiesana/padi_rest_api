<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Request;
use Core\Controller;

// Load environment
Env::load(__DIR__ . '/.env');

echo "=== TESTING CONTROLLER WITH EMPTY RULES ===\n";

// Create a test controller
class TestController extends Controller
{
    public function testEmptyRules()
    {
        return $this->validate([]); // Empty rules
    }

    public function testWithRules()
    {
        return $this->validate([
            'name' => 'required',
            'email' => 'required'
        ]);
    }
}

// Test request with empty data
$_POST = ['name' => '', 'email' => ''];
$_GET = [];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

$request = new Request();
$controller = new TestController($request);

echo "Testing with empty validation rules...\n";
try {
    $result = $controller->testEmptyRules();
    echo "Result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
}

echo "\nTesting with proper validation rules...\n";
try {
    $result = $controller->testWithRules();
    echo "Result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
}
