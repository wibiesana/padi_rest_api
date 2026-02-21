# 🔍 Query Builder Documentation

The `Core\Query` class provides a fluent interface for building and executing SQL queries quickly, safely, and efficiently, similar to `yii\db\Query`.

---

## 🚀 Getting Started

You can use the Query Builder through ActiveRecord or directly.

### 1. Through ActiveRecord (Recommended)

This method automatically sets the table name and database connection based on the ActiveRecord definition.

```php
use App\Models\Post;

// Using findQuery() or findBuilder() aliases
$query = Post::findQuery();
```

### 2. Standalone Usage

Use this if you want to query a table that does not have a Model.

```php
use Core\Query;

$query = Query::find()->from('some_table_name');
```

---

## 🛠️ Query Methods

### `select($columns)`

Specifies the columns to retrieve. Defaults to `*`.

```php
$query->select(['id', 'title', 'slug']);
// or as a string
$query->select('id, title');
```

### `addSelect($columns)`

Adds columns to an existing select statement.

```php
$query->select(['id'])->addSelect(['title']);
```

### `distinct()`

Adds the `DISTINCT` keyword to the query.

```php
$query->distinct()->select('category');
```

### `from($table)`

Specifies the table (only if not using via a Model).

```php
$query->from('users');
```

### `where($condition, $params = [])`

Adds a WHERE condition. This will overwrite previous conditions.

```php
// Associative array (column = value)
$query->where(['status' => 'published', 'type' => 'post']);

// Custom operator [operator, column, value]
$query->where(['>', 'views', 100]);

// LIKE condition
$query->where(['like', 'title', 'announcement']);

// IN condition
$query->where(['id' => [1, 2, 3]]);

// BETWEEN condition
$query->where(['between', 'created_at', ['2023-01-01', '2023-12-31']]);
```

### `andWhere()` / `orWhere()`

Adds additional conditions with AND or OR operators.

```php
$query->where(['status' => 'active'])
      ->andWhere(['>', 'views', 50])
      ->orWhere(['is_featured' => 1]);
```

### Quick Where Helpers

For common conditions, you can use these helper methods:

```php
// WHERE id IN (1, 2, 3)
$query->whereIn('id', [1, 2, 3]);

// WHERE id NOT IN (4, 5)
$query->whereNotIn('id', [4, 5]);

// WHERE created_at BETWEEN '2023-01-01' AND '2023-12-31'
$query->whereBetween('created_at', '2023-01-01', '2023-12-31');

// WHERE amount NOT BETWEEN 10 AND 50
$query->whereNotBetween('amount', 10, 50);

// WHERE deleted_at IS NULL
$query->whereNull('deleted_at');

// WHERE updated_at IS NOT NULL
$query->whereNotNull('updated_at');
```

### `join($type, $table, $on)`

Adds a JOIN. Shortcuts available: `innerJoin()`, `leftJoin()`, `rightJoin()`.

```php
$query->innerJoin('users', 'users.id = posts.user_id');
```

### `orderBy($columns)`

Specifies the sorting order.

```php
$query->orderBy('created_at DESC');
// or as an array
$query->orderBy(['created_at' => SORT_DESC, 'title' => SORT_ASC]);
```

### `addOrderBy($columns)`

Adds columns to an existing order by statement.

```php
$query->orderBy('created_at DESC')->addOrderBy('title ASC');
```

### `groupBy($columns)` & `having($condition)`

```php
$query->select('category, COUNT(*) as total')
      ->groupBy('category')
      ->having('total > 5');
```

```php
$query->limit(10)->offset(20);
```

### `autoIlike(bool $value)`

Enables or disables automatic `ILIKE` conversion for PostgreSQL. Enabled by default.

```php
// Use Case-Sensitive 'LIKE' on PostgreSQL
$query->autoIlike(false)
      ->where(['like', 'name', 'Laptop']);
```

### `paginate($perPage, $page)`

Easily paginate results. Returns an array with metadata.

```php
$result = $query->paginate(20, 1);
// Returns:
// [
//    'data' => [...],
//    'total' => 150,
//    'per_page' => 20,
//    'current_page' => 1,
//    'last_page' => 8
// ]
```

### `rawSql()`

Returns the generated SQL with parameters interpolated. Useful for debugging.

```php
echo $query->where(['id' => 1])->rawSql();
// SELECT * FROM users WHERE id = '1'
```

---

## 🏃 Query Execution

After building the query, use the following methods to retrieve the results:

| Method            | Description                                                               |
| :---------------- | :------------------------------------------------------------------------ |
| `all()`           | Retrieves all rows (array of associative arrays).                         |
| `one()`           | Retrieves the first row or `null`.                                        |
| `scalar()`        | Retrieves the first column value from the first row (suitable for COUNT). |
| `column()`        | Retrieves all values from the first column as a one-dimensional array.    |
| `count($q = '*')` | Counts the number of rows.                                                |
| `sum($column)`    | Calculates the sum of a column.                                           |
| `avg($column)`    | Calculates the average of a column.                                       |
| `min($column)`    | Finds the minimum value of a column.                                      |
| `max($column)`    | Finds the maximum value of a column.                                      |
| `exists()`        | Checks if any record matches the criteria (returns boolean).              |

---

## 💡 Real-World Examples

### Search with Complex Filtering

```php
$posts = Post::findQuery()
    ->select(['posts.*', 'users.username as author'])
    ->leftJoin('users', 'users.id = posts.user_id')
    ->where(['status' => 'published'])
    ->andWhere(['like', 'title', 'announcement'])
    ->orderBy('published_at DESC')
    ->limit(5)
    ->all();
```

### Duplicate Check

```php
$exists = Post::findQuery()
    ->where(['slug' => 'this-post-title'])
    ->exists();

if ($exists) {
    // Return error or change slug
}
```

### Counting Totals by Category

```php
$total = Post::findQuery()
    ->where(['category_id' => 5])
    ->count();
```

---

## 🔒 Security

The Query Builder automatically uses **PDO Prepared Statements** for all values entered through `where()`, `andWhere()`, `orWhere()`, and `having()` methods. This ensures your application is safe from **SQL Injection** attacks.
