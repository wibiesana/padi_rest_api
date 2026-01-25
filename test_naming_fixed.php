<?php
function tableNameToModelName(string $tableName): string
{
    // Handle plural table names more carefully
    // Only remove 's' if it's actually a plural form, not part of a word like 'class'
    $singular = $tableName;

    // Common plural patterns to handle
    if (preg_match('/(.+)ies$/', $tableName, $matches)) {
        // countries -> country
        $singular = $matches[1] . 'y';
    } elseif (preg_match('/(.+)ses$/', $tableName, $matches)) {
        // classes -> class, addresses -> address
        $singular = $matches[1] . 's';
    } elseif (preg_match('/(.+[^s])s$/', $tableName, $matches)) {
        // users -> user, posts -> post (but not class -> clas)
        $singular = $matches[1];
    }

    // Convert snake_case to PascalCase
    return str_replace('_', '', ucwords($singular, '_'));
}

$testTables = [
    'exam_class',
    'exam_class_user',
    'exam_classes', // Test if this works
    'student',
    'teacher',
    'posts',
    'users',
    'addresses',
    'categories',
    'countries'
];

echo "=== TESTING IMPROVED tableNameToModelName FUNCTION ===\n";
foreach ($testTables as $table) {
    $modelName = tableNameToModelName($table);
    echo sprintf("%-20s → %s\n", $table, $modelName);
}
