<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Define project root path
define('PADI_ROOT', dirname(__DIR__));

use Wibiesana\Padi\Core\Env;
use Wibiesana\Padi\Core\Migrator;
use Wibiesana\Padi\Core\DatabaseManager;

Env::load(__DIR__ . '/../.env');

$command = $argv[1] ?? 'migrate';

// Parse options
$options = [];
foreach ($argv as $arg) {
    if (strpos($arg, '--') === 0) {
        $parts = explode('=', substr($arg, 2), 2);
        $options[$parts[0]] = $parts[1] ?? true;
    }
}

$migrator = new Migrator();

echo "\nDatabase Migration Tool\n";
echo str_repeat('=', 30) . "\n";
echo "Driver: " . DatabaseManager::getDriver() . "\n";
echo "Connection: " . DatabaseManager::getDefaultConnection() . "\n";
echo str_repeat('=', 30) . "\n\n";

switch ($command) {
    case 'migrate':
        // Support --tables option
        if (isset($options['tables'])) {
            $tables = explode(',', $options['tables']);
            echo "Migrating specific tables: " . implode(', ', $tables) . "\n\n";
            $migrator->migrate($tables);
        } else {
            $migrator->migrate();
        }
        break;

    case 'rollback':
        $steps = isset($options['step']) ? (int)$options['step'] : 1;
        $migrator->rollback($steps);
        break;

    case 'make':
        $name = $argv[2] ?? 'migration';
        $filename = date('Y_m_d_His') . '_' . $name . '.php';
        $path = __DIR__ . '/../database/migrations/' . $filename;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $template = "<?php\n\nreturn [\n    'up' => function (\$db) {\n        // \$db->exec(\"CREATE TABLE ...\");\n    },\n    'down' => function (\$db) {\n        // \$db->exec(\"DROP TABLE ...\");\n    }\n];\n";
        file_put_contents($path, $template);
        echo "Migration created: $filename\n";
        break;

    case 'status':
        $migrator->status();
        break;

    default:
        echo "Unknown command: $command\n";
        echo "\nUsage:\n";
        echo "  php migrate.php migrate [--tables=users,posts]\n";
        echo "  php migrate.php rollback [--step=1]\n";
        echo "  php migrate.php make migration_name\n";
        echo "  php migrate.php status\n";
        break;
}

echo "\n";
