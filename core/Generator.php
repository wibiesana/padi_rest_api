<?php

declare(strict_types=1);

namespace Core;

/**
 * Code Generator - Similar to Yii's Gii
 * Generate ActiveRecord, Controller, and Routes automatically
 */
class Generator
{
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = dirname(__DIR__);
    }

    /**
     * Generate ActiveRecord from database table
     */
    public function generateModel(string $tableName, array $options = []): bool
    {
        $modelName = $this->tableNameToModelName($tableName);
        $namespace = $options['namespace'] ?? 'App\\Models';
        $baseNamespace = $namespace . '\\Base';
        $fillable = $options['fillable'] ?? [];
        $hidden = $options['hidden'] ?? [];

        // Get table columns from database
        if (empty($fillable)) {
            $fillable = $this->getTableColumns($tableName);
        }

        // Auto-hide sensitive fields
        $sensitiveFields = ['password', 'token', 'secret', 'key'];
        foreach ($fillable as $column) {
            if (in_array(strtolower($column), $sensitiveFields)) {
                $hidden[] = $column;
            }
        }

        // 1. Generate Base ActiveRecord (Always overwrite)
        $baseTemplate = $this->getBaseModelTemplate($modelName, $tableName, $fillable, $hidden, $baseNamespace);
        $baseDir = $this->baseDir . '/app/Models/Base';
        if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

        $baseFilePath = $baseDir . '/' . $modelName . '.php';
        file_put_contents($baseFilePath, $baseTemplate);
        echo "✓ Base ActiveRecord {$modelName} created/updated at {$baseFilePath}\n";

        // 2. Generate Concrete ActiveRecord (Only if not exists)
        $template = $this->getModelTemplate($modelName, $namespace, $baseNamespace);
        $filePath = $this->baseDir . '/app/Models/' . $modelName . '.php';

        if (file_exists($filePath)) {
            echo "ℹ️  ActiveRecord {$modelName} already exists. Skipping to preserve custom logic.\n";
        } else {
            file_put_contents($filePath, $template);
            echo "✓ ActiveRecord {$modelName} created successfully at {$filePath}\n";
        }

        return true;
    }

    /**
     * Generate Controller with CRUD operations
     */
    public function generateController(string $modelName, array $options = []): bool
    {
        $controllerName = $modelName . 'Controller';
        $namespace = $options['namespace'] ?? 'App\\Controllers';
        $baseNamespace = $namespace . '\\Base';
        $modelNamespace = $options['model_namespace'] ?? 'App\\Models';

        // 1. Get schema and validation rules
        $tableName = $this->modelNameToTableName($modelName);
        $schema = $this->getTableSchema($tableName);
        $validationRules = $this->generateValidationRules($schema, $tableName);

        // 2. Generate Base Controller (Always overwrite)
        $baseTemplate = $this->getBaseControllerTemplate($modelName, $controllerName, $baseNamespace, $modelNamespace, $validationRules);
        $baseDir = $this->baseDir . '/app/Controllers/Base';
        if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

        $baseFilePath = $baseDir . '/' . $controllerName . '.php';
        file_put_contents($baseFilePath, $baseTemplate);
        echo "✓ Base Controller {$controllerName} created/updated at {$baseFilePath}\n";

        // 3. Generate Concrete Controller (Only if not exists)
        $template = $this->getControllerTemplate($controllerName, $namespace, $baseNamespace);
        $filePath = $this->baseDir . '/app/Controllers/' . $controllerName . '.php';

        if (file_exists($filePath)) {
            echo "ℹ️  Controller {$controllerName} already exists. Skipping to preserve custom logic.\n";
        } else {
            file_put_contents($filePath, $template);
            echo "✓ Controller {$controllerName} created successfully at {$filePath}\n";
        }

        return true;
    }

    /**
     * Generate Routes for a resource
     */
    public function generateRoutes(string $resourceName, array $options = []): string
    {
        $controllerName = $this->tableNameToModelName($resourceName) . 'Controller';
        $prefix = $options['prefix'] ?? strtolower($resourceName);
        $middleware = $options['middleware'] ?? [];
        $protected = $options['protected'] ?? ['store', 'update', 'destroy'];

        $routes = $this->getRoutesTemplate($prefix, $controllerName, $middleware, $protected);

        if ($options['write'] ?? false) {
            $this->appendRoutesToFile($routes, $prefix);
        } else {
            echo "💡 Routes generated. Add this to routes/api.php manually or use --write flag:\n\n";
            echo $routes . "\n";
        }

        return $routes;
    }

    /**
     * Generate complete CRUD (Model + Controller + Routes)
     */
    public function generateCrud(string $tableName, array $options = []): bool
    {
        echo "Generating CRUD for table: {$tableName}\n";
        echo str_repeat('=', 60) . "\n\n";

        // Generate Model
        echo "1. Generating Model...\n";
        $this->generateModel($tableName, $options);

        // Generate Controller
        echo "\n2. Generating Controller...\n";
        $modelName = $this->tableNameToModelName($tableName);
        $this->generateController($modelName, $options);

        // Generate Routes
        echo "\n3. Generating Routes...\n";
        $this->generateRoutes($tableName, $options);

        // Generate Postman Collection
        echo "\n4. Generating Postman Collection...\n";
        $this->generatePostmanCollection($tableName, $options);

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "✓ CRUD generation completed!\n";

        return true;
    }

    /**
     * Get table columns from database
     */
    private function getTableColumns(string $tableName): array
    {
        $schema = $this->getTableSchema($tableName);
        $columns = array_keys($schema);

        // Exclude common auto-generated columns
        $exclude = ['id', 'created_at', 'updated_at'];
        return array_diff($columns, $exclude);
    }

    /**
     * Get full table schema from database
     */
    private function getTableSchema(string $tableName): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("DESCRIBE {$tableName}");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $schema = [];
            foreach ($rows as $row) {
                $schema[$row['Field']] = $row;
            }
            return $schema;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate validation rules based on table schema
     */
    private function generateValidationRules(array $schema, string $tableName): array
    {
        $rules = [];
        $exclude = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($schema as $column => $info) {
            if (in_array($column, $exclude)) continue;
            if (strpos($info['Extra'] ?? '', 'auto_increment') !== false) continue;

            $columnRules = [];

            // Required? (Not nullable and no default)
            if (($info['Null'] ?? 'YES') === 'NO' && ($info['Default'] ?? null) === null) {
                $columnRules[] = 'required';
            }

            // Type rules
            $type = strtolower($info['Type'] ?? '');
            if (strpos($type, 'int') !== false) {
                $columnRules[] = 'integer';
            } elseif (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
                $columnRules[] = 'numeric';
            }

            // Max length for varchar
            if (preg_match('/varchar\((\d+)\)/', $type, $matches)) {
                $columnRules[] = 'max:' . $matches[1];
            }

            // Email check
            if (strpos($column, 'email') !== false) {
                $columnRules[] = 'email';
            }

            // Unique check
            if (($info['Key'] ?? '') === 'UNI') {
                $columnRules[] = "unique:{$tableName},{$column}";
            }

            if (!empty($columnRules)) {
                $rules[$column] = implode('|', $columnRules);
            }
        }

        return $rules;
    }

    private function getTableNameFromSchema(array $schema): string
    {
        // Guess table name if unknown (fallback)
        foreach ($schema as $info) {
            // Usually there's no easy way to get table name from DESCRIBE result directly
            // but we can pass it down if needed. For now, let's keep it placeholder or pass it.
            return 'TABLE_NAME';
        }
        return 'table';
    }

    /**
     * Convert Model name to table name
     */
    private function modelNameToTableName(string $modelName): string
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
        if (substr($table, -1) !== 's') {
            $table .= 's';
        }
        return $table;
    }

    /**
     * Convert table name to Model name
     */
    private function tableNameToModelName(string $tableName): string
    {
        // Remove trailing 's' for plural table names
        $singular = rtrim($tableName, 's');

        // Convert snake_case to PascalCase
        return str_replace('_', '', ucwords($singular, '_'));
    }

    /**
     * Get Base Model template
     */
    private function getBaseModelTemplate(string $modelName, string $tableName, array $fillable, array $hidden, string $namespace): string
    {
        $fillableStr = "'" . implode("', '", $fillable) . "'";
        $hiddenStr = empty($hidden) ? '' : "'" . implode("', '", $hidden) . "'";

        // Generate smart search query using fillable columns
        // Only use likely text columns for search
        $searchableColumns = [];
        $textColumnKeywords = ['name', 'title', 'description', 'content', 'email', 'username', 'category', 'type', 'status', 'code', 'sku'];

        foreach ($fillable as $column) {
            // Include column if it contains common text keywords
            foreach ($textColumnKeywords as $keyword) {
                if (stripos($column, $keyword) !== false) {
                    $searchableColumns[] = $column;
                    break;
                }
            }
        }

        // If no searchable columns found, use first fillable column as fallback
        if (empty($searchableColumns) && !empty($fillable)) {
            $searchableColumns = [reset($fillable)];
        }

        // Build WHERE clause
        $whereConditions = [];
        $paramBindings = [];
        foreach ($searchableColumns as $index => $column) {
            $paramName = "keyword" . ($index > 0 ? ($index + 1) : '');
            $whereConditions[] = "{$column} LIKE :{$paramName}";
            $paramBindings[] = "            '{$paramName}' => \$searchTerm";
        }

        $whereClause = !empty($whereConditions) ? implode("\n                   OR ", $whereConditions) : "id LIKE :keyword";
        $paramsStr = !empty($paramBindings) ? implode(",\n", $paramBindings) : "            'keyword' => \$searchTerm";

        return <<<PHP
<?php

namespace {$namespace};

use Core\ActiveRecord;

class {$modelName} extends ActiveRecord
{
    protected string \$table = '{$tableName}';
    protected string \$primaryKey = 'id';
    
    protected array \$fillable = [
        {$fillableStr}
    ];
    
    protected array \$hidden = [{$hiddenStr}];
    
    /**
     * Search {$tableName}
     */
    public function search(string \$keyword): array
    {
        \$searchTerm = "%\$keyword%";
        
        \$sql = "SELECT * FROM {\$this->table} 
                WHERE {$whereClause}
                LIMIT 100";
        
        return \$this->query(\$sql, [
{$paramsStr}
        ]);
    }
}

PHP;
    }


    /**
     * Get Concrete Model template
     */
    private function getModelTemplate(string $modelName, string $namespace, string $baseNamespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use {$baseNamespace}\\{$modelName} as BaseModel;

class {$modelName} extends BaseModel
{
    // Add custom model logic here
}

PHP;
    }

    /**
     * Get Base Controller template
     */
    private function getBaseControllerTemplate(string $modelName, string $controllerName, string $namespace, string $modelNamespace, array $validationRules): string
    {
        $modelVar = lcfirst($modelName);
        $resourceName = strtolower($modelName);

        $storeRules = "";
        $updateRules = "";

        foreach ($validationRules as $column => $rule) {
            $storeRules .= "            '{$column}' => '{$rule}',\n";

            // For update, unique rules need to ignore current ID
            if (strpos($rule, 'unique:') !== false) {
                $updateRules .= "            '{$column}' => '{$rule},' . \$id,\n";
            } else {
                $updateRules .= "            '{$column}' => '{$rule}',\n";
            }
        }

        $storeRules = rtrim($storeRules, ",\n");
        $updateRules = rtrim($updateRules, ",\n");

        return <<<PHP
<?php

namespace {$namespace};

use Core\Controller;
use {$modelNamespace}\\{$modelName};

class {$controllerName} extends Controller
{
    protected {$modelName} \$model;
    
    public function __construct(?\Core\Request \$request = null)
    {
        parent::__construct(\$request);
        \$this->model = new {$modelName}();
    }
    
    /**
     * Get all {$resourceName}s with pagination
     * GET /{$resourceName}s
     */
    public function index(): void
    {
        \$page = max(1, (int)\$this->request->query('page', 1)); // Min page 1
        \$perPage = min(100, max(1, (int)\$this->request->query('per_page', 10))); // Max 100 per page
        \$search = \$this->request->query('search');
        
        if (\$search) {
            // Limit search query length to prevent abuse
            \$search = substr(\$search, 0, 255);
            \$data = \$this->model->search(\$search);
            \$this->success(['data' => \$data]);
            return;
        }
        
        \$result = \$this->model->paginate(\$page, \$perPage);
        \$this->success(\$result);
    }
    
    /**
     * Get all {$resourceName}s without pagination
     * GET /{$resourceName}s/all
     */
    public function all(): void
    {
        \$data = \$this->model::findQuery()->all();
        \$this->success(['data' => \$data]);
    }
    
    /**
     * Get single {$resourceName}
     * GET /{$resourceName}s/{id}
     */
    public function show(): void
    {
        \$id = \$this->request->param('id');
        \${$resourceName} = \$this->model->find(\$id);
        
        if (!\${$resourceName}) {
            \$this->notFound('{$modelName} not found');
        }
        
        \$this->success(\${$resourceName});
    }
    
    /**
     * Create new {$resourceName}
     * POST /{$resourceName}s
     */
    public function store(): void
    {
        \$validated = \$this->validate([
{$storeRules}
        ]);
        
        \$id = \$this->model->create(\$validated);
        
        \$this->success([
            'id' => \$id,
            '{$resourceName}' => \$this->model->find(\$id)
        ], '{$modelName} created successfully', 201);
    }
    
    /**
     * Update {$resourceName}
     * PUT /{$resourceName}s/{id}
     */
    public function update(): void
    {
        \$id = \$this->request->param('id');
        \${$resourceName} = \$this->model->find(\$id);
        
        if (!\${$resourceName}) {
            \$this->notFound('{$modelName} not found');
        }
        
        \$validated = \$this->validate([
{$updateRules}
        ]);
        
        \$this->model->update(\$id, \$validated);
        
        \$this->success(
            \$this->model->find(\$id),
            '{$modelName} updated successfully'
        );
    }
    
    /**
     * Delete {$resourceName}
     * DELETE /{$resourceName}s/{id}
     */
    public function destroy(): void
    {
        \$id = \$this->request->param('id');
        \${$resourceName} = \$this->model->find(\$id);
        
        if (!\${$resourceName}) {
            \$this->notFound('{$modelName} not found');
        }
        
        \$this->model->delete(\$id);
        
        \$this->success(null, '{$modelName} deleted successfully');
    }
}

PHP;
    }

    /**
     * Get Concrete Controller template
     */
    private function getControllerTemplate(string $controllerName, string $namespace, string $baseNamespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use {$baseNamespace}\\{$controllerName} as BaseController;

class {$controllerName} extends BaseController
{
    /**
     * Override methods here to add custom logic.
     */
}

PHP;
    }

    /**
     * Get Routes template
     */
    private function getRoutesTemplate(string $prefix, string $controllerName, array $middleware, array $protected): string
    {
        $middlewareStr = empty($middleware) ? '' : ", 'middleware' => ['" . implode("', '", $middleware) . "']";

        $routes = "// {$prefix} routes\n";
        $routes .= "\$router->group(['prefix' => '{$prefix}'{$middlewareStr}], function(\$router) {\n";
        $routes .= "    \$router->get('/', '{$controllerName}@index');\n";
        $routes .= "    \$router->get('/all', '{$controllerName}@all');\n";
        $routes .= "    \$router->get('/{id}', '{$controllerName}@show');\n";

        if (in_array('store', $protected)) {
            $routes .= "    \$router->post('/', '{$controllerName}@store')->middleware('AuthMiddleware');\n";
        } else {
            $routes .= "    \$router->post('/', '{$controllerName}@store');\n";
        }

        if (in_array('update', $protected)) {
            $routes .= "    \$router->put('/{id}', '{$controllerName}@update')->middleware('AuthMiddleware');\n";
        } else {
            $routes .= "    \$router->put('/{id}', '{$controllerName}@update');\n";
        }

        if (in_array('destroy', $protected)) {
            $routes .= "    \$router->delete('/{id}', '{$controllerName}@destroy')->middleware('AuthMiddleware');\n";
        } else {
            $routes .= "    \$router->delete('/{id}', '{$controllerName}@destroy');\n";
        }

        $routes .= "});\n";

        return $routes;
    }

    /**
     * Append routes to api.php file
     */
    private function appendRoutesToFile(string $routes, string $prefix): void
    {
        $filePath = $this->baseDir . '/routes/api.php';
        if (!file_exists($filePath)) return;

        $content = file_get_contents($filePath);

        // Check if routes for this prefix already exist (check both single and double quotes)
        if (
            strpos($content, "['prefix' => '{$prefix}'") !== false ||
            strpos($content, "['prefix' => \"{$prefix}\"") !== false ||
            strpos($content, "[\"prefix\" => '{$prefix}'") !== false ||
            strpos($content, "[\"prefix\" => \"{$prefix}\"") !== false
        ) {
            echo "⚠️ Routes for '{$prefix}' already exist in api.php. Skipping auto-append.\n";
            return;
        }

        // Find the last return statement
        $lastReturnPos = strrpos($content, 'return $router;');

        if ($lastReturnPos !== false) {
            $newContent = substr($content, 0, $lastReturnPos) . "\n" . $routes . "\n" . substr($content, $lastReturnPos);
            file_put_contents($filePath, $newContent);
            echo "✓ Routes for '{$prefix}' automatically appended to routes/api.php\n";
        } else {
            // Just append at the end if no return found
            file_put_contents($filePath, "\n" . $routes, FILE_APPEND);
            echo "✓ Routes for '{$prefix}' appended to end of routes/api.php\n";
        }
    }

    /**
     * Generate Postman Collection for REST API
     */
    public function generatePostmanCollection(string $tableName, array $options = []): bool
    {
        $modelName = $this->tableNameToModelName($tableName);
        $resourceName = strtolower($modelName);
        $prefix = $options['prefix'] ?? strtolower($tableName);
        $protected = $options['protected'] ?? ['store', 'update', 'destroy'];

        // Get schema for generating sample data
        $schema = $this->getTableSchema($tableName);
        $sampleData = $this->generateSampleData($schema);

        // Get base URL from env or use default
        $baseUrl = \Core\Env::get('APP_URL', 'http://localhost:8000');
        $apiPrefix = '/api';

        $collection = [
            'info' => [
                'name' => "{$modelName} API",
                'description' => "REST API endpoints for {$modelName} resource",
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                '_exporter_id' => '0'
            ],
            'item' => [
                [
                    'name' => "Get All {$modelName}s (Paginated)",
                    'request' => [
                        'method' => 'GET',
                        'header' => [],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}?page=1&per_page=10",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix],
                            'query' => [
                                ['key' => 'page', 'value' => '1'],
                                ['key' => 'per_page', 'value' => '10']
                            ]
                        ]
                    ],
                    'response' => []
                ],
                [
                    'name' => "Search {$modelName}s",
                    'request' => [
                        'method' => 'GET',
                        'header' => [],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}?search=keyword",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix],
                            'query' => [
                                ['key' => 'search', 'value' => 'keyword']
                            ]
                        ]
                    ],
                    'response' => []
                ],
                [
                    'name' => "Get All {$modelName}s (No Pagination)",
                    'request' => [
                        'method' => 'GET',
                        'header' => [],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}/all",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix, 'all']
                        ]
                    ],
                    'response' => []
                ],
                [
                    'name' => "Get Single {$modelName}",
                    'request' => [
                        'method' => 'GET',
                        'header' => [],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}/1",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix, '1']
                        ]
                    ],
                    'response' => []
                ],
                [
                    'name' => "Create {$modelName}",
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            ['key' => 'Content-Type', 'value' => 'application/json']
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix]
                        ]
                    ],
                    'response' => []
                ],
                [
                    'name' => "Update {$modelName}",
                    'request' => [
                        'method' => 'PUT',
                        'header' => [
                            ['key' => 'Content-Type', 'value' => 'application/json']
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}/1",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix, '1']
                        ]
                    ],
                    'response' => []
                ],
                [
                    'name' => "Delete {$modelName}",
                    'request' => [
                        'method' => 'DELETE',
                        'header' => [],
                        'url' => [
                            'raw' => "{{base_url}}{$apiPrefix}/{$prefix}/1",
                            'host' => ['{{base_url}}'],
                            'path' => [ltrim($apiPrefix, '/'), $prefix, '1']
                        ]
                    ],
                    'response' => []
                ]
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $baseUrl,
                    'type' => 'string'
                ],
                [
                    'key' => 'token',
                    'value' => '',
                    'type' => 'string'
                ]
            ]
        ];

        // Add auth header to protected endpoints
        if (!empty($protected)) {
            foreach ($collection['item'] as &$item) {
                $method = $item['request']['method'] ?? '';

                // Check if this endpoint should be protected
                $isProtected = false;
                if (in_array('store', $protected) && $method === 'POST') $isProtected = true;
                if (in_array('update', $protected) && $method === 'PUT') $isProtected = true;
                if (in_array('destroy', $protected) && $method === 'DELETE') $isProtected = true;

                if ($isProtected) {
                    $item['request']['header'][] = [
                        'key' => 'Authorization',
                        'value' => 'Bearer {{token}}',
                        'type' => 'text'
                    ];
                }
            }
        }

        // Create postman directory if not exists
        $postmanDir = $this->baseDir . '/postman';
        if (!is_dir($postmanDir)) {
            mkdir($postmanDir, 0755, true);
        }

        // Save collection to file
        $filename = strtolower($modelName) . '_api_collection.json';
        $filePath = $postmanDir . '/' . $filename;

        $jsonContent = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $jsonContent);

        echo "✓ Postman Collection created at {$filePath}\n";
        echo "  Import this file to Postman to test the API endpoints\n";

        return true;
    }

    /**
     * Generate sample data for Postman requests
     */
    private function generateSampleData(array $schema): array
    {
        $data = [];
        $exclude = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($schema as $column => $info) {
            if (in_array($column, $exclude)) continue;
            if (strpos($info['Extra'] ?? '', 'auto_increment') !== false) continue;

            $type = strtolower($info['Type'] ?? '');
            $columnLower = strtolower($column);

            // Generate appropriate sample value based on column name and type
            if (strpos($columnLower, 'email') !== false) {
                $data[$column] = 'user@example.com';
            } elseif (strpos($columnLower, 'password') !== false) {
                $data[$column] = 'password123';
            } elseif (strpos($columnLower, 'phone') !== false) {
                $data[$column] = '+1234567890';
            } elseif (strpos($columnLower, 'url') !== false || strpos($columnLower, 'website') !== false) {
                $data[$column] = 'https://example.com';
            } elseif (strpos($columnLower, 'name') !== false) {
                $data[$column] = 'Sample Name';
            } elseif (strpos($columnLower, 'title') !== false) {
                $data[$column] = 'Sample Title';
            } elseif (strpos($columnLower, 'description') !== false) {
                $data[$column] = 'This is a sample description';
            } elseif (strpos($columnLower, 'content') !== false || strpos($columnLower, 'body') !== false) {
                $data[$column] = 'This is sample content';
            } elseif (strpos($columnLower, 'price') !== false || strpos($columnLower, 'amount') !== false) {
                $data[$column] = 99.99;
            } elseif (strpos($columnLower, 'quantity') !== false || strpos($columnLower, 'stock') !== false) {
                $data[$column] = 10;
            } elseif (strpos($columnLower, 'status') !== false) {
                $data[$column] = 'active';
            } elseif (strpos($columnLower, 'date') !== false) {
                $data[$column] = date('Y-m-d');
            } elseif (strpos($columnLower, 'time') !== false) {
                $data[$column] = date('H:i:s');
            } elseif (strpos($columnLower, 'is_') !== false || strpos($columnLower, 'has_') !== false) {
                $data[$column] = true;
            } elseif (strpos($type, 'int') !== false) {
                $data[$column] = 1;
            } elseif (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
                $data[$column] = 0.0;
            } elseif (strpos($type, 'bool') !== false || strpos($type, 'tinyint(1)') !== false) {
                $data[$column] = true;
            } elseif (strpos($type, 'json') !== false) {
                $data[$column] = [];
            } elseif (strpos($type, 'text') !== false || strpos($type, 'varchar') !== false) {
                $data[$column] = 'sample text';
            } else {
                $data[$column] = 'value';
            }
        }

        return $data;
    }
}
