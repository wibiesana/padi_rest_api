<?php
function tableNameToModelName(string $tableName): string
{
    // Remove trailing 's' for plural table names
    $singular = rtrim($tableName, 's');

    // Convert snake_case to PascalCase
    return str_replace('_', '', ucwords($singular, '_'));
}

$testTables = [
    'exam_class',
    'exam_class_user',
    'student',
    'teacher',
    'posts',
    'users'
];

echo "=== TESTING tableNameToModelName FUNCTION ===\n";
foreach ($testTables as $table) {
    $modelName = tableNameToModelName($table);
    echo sprintf("%-20s → %s\n", $table, $modelName);
}
