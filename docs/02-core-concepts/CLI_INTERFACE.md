# 🛠️ Padi Console CLI (Command Line Interface)

**Padi REST API Framework v2.0.1**

---

## 📖 Overview

**Padi CLI** is the control center for your application. It replaces the separate scripts that used to reside in the `scripts/` folder with a single, consistent, and powerful interface. Built on a modern **Console Core** system, Padi CLI streamlines everything from development processes and database migrations to automated code generation.

---

## 🚀 Basic Usage

Run the main command from your terminal in the root of your project:

```bash
php padi <command> [arguments] [options]
```

To see the complete list of commands, use:

```bash
php padi help
```

---

## 🛠️ Command List

### 📂 Application (Application)

| Command          | Description                                                      |
| :--------------- | :--------------------------------------------------------------- |
| `php padi init`  | Runs the interactive Setup Wizard (configures .env, DB, & Keys). |
| `php padi serve` | Starts the local development server (Default port: 8085).        |

### 🔨 Code Generation (Make)

Used to quickly scaffold new boilerplate files.

| Command           | Example                             | Output                                                  |
| :---------------- | :---------------------------------- | :------------------------------------------------------ |
| `make:controller` | `php padi make:controller Product`  | Creates a controller in `app/Controllers/`.             |
| `make:model`      | `php padi make:model products`      | Creates an ActiveRecord model in `app/Models/`.         |
| `make:migration`  | `php padi make:migration add_stock` | Creates a new migration file in `database/migrations/`. |

### 🗄️ Database Migrations (Migrate)

Manage your database schema changes in a structured way.

| Command                     | Description                                      |
| :-------------------------- | :----------------------------------------------- |
| `php padi migrate`          | Runs all pending migrations.                     |
| `php padi migrate:status`   | Shows the status of executed/pending migrations. |
| `php padi migrate:rollback` | Rolls back the last batch of migrations.         |

### ⚡ CRUD Generator (Generate)

Full automation to create a complete API feature from a database table.

| Command                               | Description                                                          |
| :------------------------------------ | :------------------------------------------------------------------- |
| `php padi generate:crud <table_name>` | Generates Model, Controller, Resource, Routes, & Postman Collection. |
| `php padi generate:crud-all`          | Generates complete CRUD for **all** tables in the database (Bulk).   |

---

## ⚙️ Options & Flags

Padi CLI supports flexible flags to customize command execution:

### 📄 Global Options

- `--write`: Mandatory for `generate:crud` to actually write files to disk (prevents accidental overwrites).
- `--overwrite`: Allows overwriting existing **Base** files (very useful after you've changed the database schema).
- `--force`: Forces regeneration on protected tables (such as `users`).

### 🛡️ Security Options

- `--protected=all`: Automatically applies the `Auth` middleware to all generated routes.
- `--protected=none`: Makes all routes public (no authentication required).
- `--middleware=Auth,RoleMiddleware:admin`: Adds custom middleware to the generated routes.

### 🗄️ Database & Server Options

- `--tables=users,posts`: (For `migrate` only) Runs migrations exclusively for the specified tables.
- `--step=2`: (For `rollback` only) Rolls back the migrations by X steps.
- `--port=9000`: (For `serve` only) Runs the server on a specific port.
- `--host=0.0.0.0`: (For `serve` only) Changes the server bind host.

---

## 💡 Example Workflow

### 1. Starting a New Project

```bash
composer install
php padi init
php padi serve
```

### 2. Generating a Protected CRUD Feature

Suppose you just created a `products` table in your database:

```bash
# Generate the complete code with Login protection
php padi generate:crud products --write --protected=all
```

### 3. Managing Specific Migrations

```bash
# Run migrations only for the transactions and orders tables
php padi migrate --tables=transactions,orders

# Rollback the last 3 steps
php padi migrate:rollback --step=3
```

---

## 🔍 Tips & Tricks

- **Dry Run**: Use `generate:crud` without `--write` to preview which files will be created without touching the disk.
- **Quick Aliases**: You can use `php padi g` for `generate:crud` and `php padi ga` for `generate:crud-all`.

---

**Last Updated:** 2026-02-23  
**Version:** 2.0.1
