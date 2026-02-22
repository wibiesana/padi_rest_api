# 🏦 Database Transactions

This framework supports database transactions to ensure data integrity (Atomicity). If one query in a group fails, all changes will be reverted (rollback).

---

## 🛠️ Usage

There are two ways to use transactions in this framework:

### 1. Automatic Transactions (Recommended)

This method is the safest because the framework automatically performs a `commit` if successful, and a `rollback` if an exception/error occurs.

```php
use Core\Database;

try {
    $userId = Database::transaction(function() {
        // Query 1
        $user = new User();
        $id = $user->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Query 2
        $post = new Post();
        $post->create([
            'user_id' => $id,
            'title' => 'Welcome Post'
        ]);

        return $id; // This value will be returned by Database::transaction
    });
} catch (\Throwable $e) {
    // Transaction automatically ROLLBACK if it lands here
    echo "Failed: " . $e->getMessage();
}
```

---

### 2. Manual Transactions

Use this method if you need full control over when to commit or rollback.

```php
use Core\Database;

Database::beginTransaction();

try {
    $user = new User();
    $id = $user->create([...]);

    // Manual condition for rollback
    if ($someConditionFailed) {
        Database::rollback();
        return;
    }

    Database::commit();
} catch (\Exception $e) {
    Database::rollback();
    throw $e;
}
```

---

## 🏗️ Transactions with Multiple Databases

If you use the multi-database feature, you can specify the target connection by passing its name (as configured in `config/database.php`).

```php
// Transaction on 'mysql' database (default)
Database::transaction(function() { ... });

// Transaction on 'pgsql' database
Database::transaction(function() { ... }, 'pgsql');

// Manual transaction on a specific connection
Database::beginTransaction('sqlite');
Database::rollback('sqlite');
```

---

## 💡 When to Use Transactions?

- **Multi-table Inserts**: When saving data to `orders` and `order_items` tables simultaneously.
- **Financial Operations**: When performing balance transfers (deducting from A, adding to B).
- **Data Consistency**: When performing interdependent actions (e.g., deleting a User along with all their Posts).

---

[⬅️ Back to Docs Index](INDEX.md)
