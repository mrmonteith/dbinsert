Here’s a cleaned‑up version of your README with the **table structure** added and the examples aligned to your current working code. I’ve kept it concise but complete so anyone cloning the repo can get up and running quickly.

---

```markdown
# dbinsert

A small PHP utility that centralizes database inserts and logging using an OOP **trait** to reduce duplicate code. Designed to accept an associative array of `column => value` pairs, build an `INSERT` statement, execute it via PDO, and append a log entry for each operation.

---

## Contents
- **`example.php`** — simple usage example that builds a `$cars` array and calls the insert function.  
- **`dbinsert.class.php`** — thin class that `use`s the `DBInsertTrait`.  
- **`dbinsert.trait.php`** — core trait that builds the query, executes it, sets session messages, and writes logs.  
- **`config.php`** — project configuration (database credentials and settings).  
- **`logs/`** — directory where per‑operation log files are written.

---

## Database structure

The utility expects a table with columns matching the keys in your `$data` array.  
Example schema for the `test` table:

```sql
CREATE TABLE test (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Car1 VARCHAR(255) NOT NULL,
  Car2 VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Indexes for faster lookups:

```sql
ALTER TABLE test ADD INDEX idx_car1 (Car1);
ALTER TABLE test ADD INDEX idx_car2 (Car2);
```

---

## Quick start

1. **Install / place files** in your project folder:
   - `dbinsert.trait.php`
   - `dbinsert.class.php`
   - `config.php` (see example below)
   - your script that calls the class (e.g., `example.php`)

2. **Example `config.php`** (adjust to your environment):
```php
<?php
// config.php - example
$dsn     = 'mysql:host=127.0.0.1;dbname=testdb;charset=utf8mb4';
$db_user = 'db_user';
$db_pass = 'db_password';
```

3. **Example usage** (save as `example.php`):
```php
<?php
session_start();

require_once 'config.php';
require_once 'dbinsert.class.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("PDO connection failed: " . $e->getMessage());
}

$cars = [
  'Car1' => 'Mustang',
  'Car2' => 'Ecosport'
];

$instance = new DBInsertClass($pdo, __DIR__ . '/logs');
$Name     = 'cars_print';   // log filename prefix
$Table    = 'test';         // target table name

$result = $instance->DBInsertFunction($Name, $cars, $Table);
echo $result;
```

4. **Run** the script in a web server or CLI environment that has access to the configured database and write permissions for the `logs/` folder.

---

## How it works

- The trait **accepts three parameters**: `$name` (used for log filename), `$data` (associative array of column => value), and `$table` (target table).
- It **builds column names and placeholders** from the array and constructs a parameterized `INSERT` SQL string.
- Executes the query via **PDO prepared statements**.
- Results and errors are written to a per‑name log file under the `logs/` directory.
- Session messages are set to indicate success or failure for UI feedback.

---

## Important notes & recommended improvements

- **Security:** Always use parameterized prepared statements (already implemented) to prevent SQL injection.  
- **Empty data checks:** Validate that `$data` is non‑empty and keys are valid column names.  
- **Type handling:** Bind integers, nulls, and strings explicitly.  
- **Transactions:** Wrap multi‑row operations in transactions for atomicity.  
- **Error handling:** Capture and log exceptions robustly; avoid echoing raw DB errors to users.  
- **Config loading:** Keep credentials out of version control; use `.gitignore` and provide `config.example.php`.  
- **Logging path:** Make log path configurable for CLI vs web contexts.  
- **Unit tests:** Add tests for query building and edge cases.  

---

## Troubleshooting

- **PDO driver missing:** Ensure the required PDO driver (e.g. `pdo_mysql`) is installed and enabled in PHP.  
- **Permissions:** Ensure the web server or CLI user can write to the `logs/` directory.  
- **Null/empty `$data`:** If `$data` is empty, the trait will return a failure message.  
- **Unexpected errors:** Check the log file named `{name}.log` in the `logs/` folder for the raw query and any captured PDO error messages.

---

## License

This project is available under the **MIT License**. Add a `LICENSE` file with the standard MIT text.

---

## Author
**Michael Monteith** — created 2024‑04‑15.
```

---
