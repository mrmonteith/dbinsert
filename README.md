# dbinsert / dbcrud

A small PHP utility that centralizes database CRUD operations and logging using an OOP **trait**. Accepts associative arrays of `column => value` pairs, builds parameterized SQL, executes via PDO, and appends per‑operation log entries. Originally built as `DBInsertTrait`, now expanded into `DBCrudTrait` for full Create, Read, Update, Delete support.

---

## Contents
- **`dbcrud.trait.php`** — core trait implementing `DBInsertFunction`, `DBUpdateFunction`, `DBDeleteFunction`, and `DBReadFunction`.  
- **`dbinsert.class.php`** — thin wrapper class that `use`s the trait and provides `getPDO()` and `getLogPath()`.  
- **`config.example.php`** — example configuration (PDO DSN and credentials).  
- **`dbcrudtest.php`** — test script demonstrating Insert, Update, Read, Delete.  
- **`logs/`** — directory where per‑operation log files are written.  
- **`.gitignore`** — recommended ignores for config, logs, and environment files.  
- **`LICENSE`** — MIT license.  

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

Recommended indexes:

```sql
ALTER TABLE test ADD INDEX idx_car1 (Car1);
ALTER TABLE test ADD INDEX idx_car2 (Car2);
```

---

## Quick start

1. Copy `config.example.php` to `config.php` and update credentials.  
2. Place `dbcrud.trait.php`, `dbinsert.class.php`, and `dbcrudtest.php` in your project folder.  
3. Ensure `logs/` is writable or let the class create it.  
4. Run the test script:
   ```bash
   php -f dbcrudtest.php
   ```
   or open it in a browser under a PHP‑enabled web server.

---

## CRUD usage examples

Here’s how to use each function in `DBCrudTrait`:

### Insert
```php
$insertData = ['Car1' => 'Mustang', 'Car2' => 'Ecosport'];
$result = $instance->DBInsertFunction('cars_insert', $insertData, 'test');
```

### Update
```php
$updateData = ['Car2' => 'Explorer'];
$where = ['Car1' => 'Mustang'];
$result = $instance->DBUpdateFunction('cars_update', $updateData, 'test', $where);
```

### Read
```php
$where = ['Car1' => 'Mustang'];
$rows = $instance->DBReadFunction('cars_read', 'test', $where);
print_r($rows);
```

### Delete
```php
$where = ['Car1' => 'Mustang', 'Car2' => 'Explorer'];
$result = $instance->DBDeleteFunction('cars_delete', 'test', $where);
```

Each operation writes to a log file (`logs/{name}.log`) and sets a session message if sessions are active.

---

## How it works

- **Parameterization:** All operations use PDO prepared statements with named placeholders to avoid SQL injection.  
- **Insert:** Builds placeholders from array keys and binds values.  
- **Update:** Builds `SET` clauses with `:set_` placeholders and `WHERE` clauses with `:where_` placeholders.  
- **Delete:** Builds `WHERE` clauses with `:where_` placeholders.  
- **Read:** Executes `SELECT *` with optional `WHERE` conditions; returns an array of rows.  
- **Logging:** Each operation writes a timestamped entry to `{logs}/{name}.log`.  
- **Session messages:** Trait sets `$_SESSION['message_status']` and `$_SESSION['message']` when a session is active.  
- **Extensibility:** The wrapper class injects a `PDO` instance and a log path; the trait expects `getPDO()` and `getLogPath()` to be implemented.

---

## Important notes & recommended improvements

- **Security:** Always validate or whitelist table and column names before using them in queries.  
- **Schema validation:** Consider introspecting the table or maintaining a whitelist of allowed columns.  
- **Type binding:** Bind values with explicit PDO types (e.g., `PDO::PARAM_INT`) where appropriate.  
- **Transactions:** Wrap multi‑step operations in transactions when atomicity is required.  
- **Error handling:** Log PDO exceptions; expose richer error details to admin logs while keeping user messages generic.  
- **Config management:** Keep real credentials out of version control; provide `config.example.php` and add `config.php` to `.gitignore`.  
- **Logging path:** Make the log path configurable for CLI vs web contexts.  
- **Unit tests:** Add tests for SQL generation and edge cases.  

---

## License

This project is available under the **MIT License**. See LICENSE file.

---

## Author
**Michael Monteith** — created 2024‑04‑15.
```

---

This version of the README now has a **CRUD usage section** with clear examples for Insert, Update, Read, and Delete, plus the rest of the documentation scaffolded around it. Would you like me to also add a **unit test skeleton** (PHPUnit) so you can validate each CRUD function automatically?
