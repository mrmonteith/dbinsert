<?php
// File: dbinsert.trait.php - Use it to do all the insert and logging for the
//          label scripts
//  This dbisert trait was meant to be more generic to use in other classes. 
//  *** Need to check for empty data in the function ***
// Created: 4-15-2024
// By: Michael Monteith


// dbinsert.trait.php
// Generic insert trait using parameterized queries and robust logging.

trait DBInsertTrait
{
    /**
     * Insert associative array $data into $table and log the operation.
     * Returns 'SUCCESS' or 'FAIL: <reason>'.
     */
    public function DBInsertFunction(string $name, array $data, string $table): string
    {
        // Basic validation
        if (empty($data) || empty($table)) {
            return 'FAIL: empty data or table';
        }

        // Validate column names (simple whitelist: letters, numbers, underscore)
        $columns = array_keys($data);
        foreach ($columns as $col) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $col)) {
                $this->writeLog($name, "Invalid column name: {$col}");
                return 'FAIL: invalid column name';
            }
        }

        // Build placeholders and SQL
        $placeholders = array_map(fn($c) => ':' . $c, $columns);
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        // Execute with prepared statement
        try {
            $pdo = $this->getPDO(); // provided by the class
            $stmt = $pdo->prepare($sql);

            foreach ($data as $col => $val) {
                // bind nulls explicitly
                if ($val === null) {
                    $stmt->bindValue(':' . $col, null, PDO::PARAM_NULL);
                } elseif (is_int($val)) {
                    $stmt->bindValue(':' . $col, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(':' . $col, (string)$val, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $result = ($stmt->rowCount() >= 1) ? 'SUCCESS' : 'FAIL: no rows affected';
        } catch (PDOException $e) {
            $this->writeLog($name, "PDO Error: " . $e->getMessage());
            return 'FAIL: PDO exception';
        }

        // Session messages (caller must have started session if needed)
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['message_status'] = ($result === 'SUCCESS') ? 1 : 0;
            $_SESSION['message'] = ($result === 'SUCCESS') ? 'Insert succeeded.' : 'Insert failed.';
        }

        // Log SQL and result (do not log sensitive values)
        $this->writeLog($name, $result . " - " . $sql);

        return $result;
    }

    // Helper: write to log file (class must provide getLogPath())
    protected function writeLog(string $name, string $message): void
    {
        $logPath = method_exists($this, 'getLogPath') ? $this->getLogPath() : (__DIR__ . DIRECTORY_SEPARATOR . 'logs');
        if (!is_dir($logPath)) {
            @mkdir($logPath, 0755, true);
        }
        $today = (new DateTime('now', new DateTimeZone('America/New_York')))->format('Y-m-d H:i:s T');
        $entry = $today . ' - ' . $message . PHP_EOL;
        @file_put_contents(rtrim($logPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.log', $entry, FILE_APPEND);
    }

    // The trait expects the using class to implement getPDO(): PDO
    abstract protected function getPDO(): PDO;
}
