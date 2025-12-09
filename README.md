# dbinsert

A small PHP utility that centralizes database CRUD operations and logging using an OOP **trait**. Accepts associative arrays of `column => value` pairs, builds parameterized SQL, executes via PDO, and appends per-operation log entries.

---

## Contents
- **`dbcrud.trait.php`** — core trait implementing `DBInsertFunction`, `DBUpdateFunction`, `DBDeleteFunction`, and `DBReadFunction`. Uses PDO prepared statements, session messages, and file logging.  
- **`dbinsert.class.php`** — thin wrapper class that `use`s the trait and provides `getPDO()` and `getLogPath()` implementations.  
- **`config.php`** — example configuration (PDO DSN and credentials).  
- **`dbcrudtest.php`** — test script demonstrating Insert, Update, Read, Delete.  
- **`logs/`** — directory where per‑operation log files are written.  
- **`README.md`** — this file.  
- **`LICENSE`** — MIT license (add standard MIT text).

---

## Database structure

Example table used in the examples:

```sql
CREATE TABLE test (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Car1 VARCHAR(255) NOT NULL,
  Car2 VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Recommended indexes (optional):

```sql
ALTER TABLE test ADD INDEX idx_car1 (Car1);
ALTER TABLE test ADD INDEX idx_car2 (Car2);
```

---

## Quick start

1. **Place files** in your project folder:
   - `dbcrud.trait.php`
   - `dbinsert.class.php`
   - `config.php`
   - `dbcrudtest.php`
   - create a writable `logs/` directory (or let the class create it)

2. **Example `config.php`** (local dev):
```php
<?php
// config.php - local development (DO NOT commit real credentials)
error_reporting(E_ALL);
ini_set('display_errors', '1');

$dsn     = 'mysql:host=127.0.0.1;dbname=testdb;charset=utf8mb4';
$db_user = 'db_user';
$db_pass = 'db_pass';

// optional legacy variables
$servername = '127.0.0.1';
$dbname     = 'testdb';
$username   = $db_user;
$password   = $db_pass;
```

3. **Example usage** (core pattern):
```php
<?php
require_once 'config.php';
require_once 'dbinsert.class.php';

$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$instance = new DBInsertClass($pdo, __DIR__ . '/logs');

$insertData = ['Car1' => 'Mustang', 'Car2' => 'Ecosport'];
$instance->DBInsertFunction('cars_insert', $insertData, 'test');

$updateData = ['Car2' => 'Explorer'];
$instance->DBUpdateFunction('cars_update', $updateData, 'test', ['Car1' => 'Mustang']);

$rows = $instance->DBReadFunction('cars_read', 'test', ['Car1' => 'Mustang']);

$instance->DBDeleteFunction('cars_delete', 'test', ['Car1' => 'Mustang', 'Car2' => 'Explorer']);
```

---

## How it works

- **Parameterization:** All operations use PDO prepared statements with named placeholders to avoid SQL injection.  
- **Insert:** Builds placeholders from array keys and binds values.  
- **Update:** Builds `SET` clauses with `:set_` prefixed placeholders and `WHERE` clauses with `:where_` placeholders.  
- **Delete:** Builds `WHERE` clauses with `:where_` placeholders.  
- **Read:** `SELECT *` with optional `WHERE` conditions; returns `array` of rows.  
- **Logging:** Each operation writes a timestamped entry to `{logs}/{name}.log`.  
- **Session messages:** Trait sets `$_SESSION['message_status']` and `$_SESSION['message']` when a session is active.  
- **Extensibility:** The wrapper class injects a `PDO` instance and a log path; the trait expects `getPDO()` and `getLogPath()` to be implemented.

---

## Important notes & recommended improvements

- **Security (critical):** The trait uses parameterized queries; ensure you never concatenate untrusted input into SQL identifiers (table/column names). Validate or whitelist table and column names before use.  
- **Schema validation:** Consider introspecting the table or maintaining a whitelist of allowed columns to prevent invalid identifiers.  
- **Type binding:** For stricter behavior, bind values with explicit PDO types (e.g., `PDO::PARAM_INT`) where appropriate.  
- **Transactions:** Wrap multi-step operations in transactions when atomicity is required.  
- **Error handling:** The trait logs PDO exceptions; consider exposing richer error details to admin logs while keeping user messages generic.  
- **Config management:** Keep real credentials out of version control; provide `config.example.php` and add `config.php` to `.gitignore`.  
- **Logging path:** Make the log path configurable and avoid relying on `$_SERVER['DOCUMENT_ROOT']` for CLI usage.  
- **Unit tests:** Extract SQL-building logic into testable methods and add unit tests for edge cases.  
- **Session usage:** Ensure `session_start()` is called before relying on session messages.

---

## Example files

- **`dbcrud.trait.php`** — contains `DBInsertFunction`, `DBUpdateFunction`, `DBDeleteFunction`, `DBReadFunction`, `setSessionMessage()`, and `writeLog()`.  
- **`dbinsert.class.php`** — simple wrapper that `use`s `DBCrudTrait` and implements `getPDO()` and `getLogPath()`.

If you want, I can paste the complete contents of those two files and the test script into this repo layout so you can copy them directly.

---

## License

This project is available under the **MIT License**. Add a `LICENSE` file with the standard MIT text and update the copyright.

---

## Author
**Michael Monteith** — created 2024-04-15.
