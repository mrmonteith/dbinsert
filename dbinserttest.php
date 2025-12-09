<?php
// dbinserttest.php
// Test script for DBInsertClass
// Created: 2024-04-15 - Michael Monteith
//
// This script:
//  - loads config.php
//  - builds a PDO connection (supports either $dsn/$db_user/$db_pass or $servername/$dbname/$username/$password)
//  - instantiates DBInsertClass with the PDO instance
//  - runs a sample insert and prints the result
//
// Place this file in the same directory as dbinsert.class.php, dbinsert.trait.php and config.php.
// Ensure logs/ is writable by the PHP process or the script will attempt to create it.

declare(strict_types=1);

session_start();

// show errors for local testing; remove or disable in production
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dbinsert.class.php';

/**
 * Build a PDO instance from available config variables.
 *
 * Accepts either:
 *  - $dsn, $db_user, $db_pass
 * or
 *  - $servername, $dbname, $username, $password  (will build a MySQL DSN)
 *
 * Adjust this function if you use a different DB driver (pgsql, sqlite, etc.).
 *
 * @return PDO
 * @throws RuntimeException
 */
function buildPDOFromConfig(): PDO
{
    // Prefer explicit DSN variables if present
    if (!empty($GLOBALS['dsn']) && array_key_exists('db_user', $GLOBALS) && array_key_exists('db_pass', $GLOBALS)) {
        $dsn = $GLOBALS['dsn'];
        $user = $GLOBALS['db_user'];
        $pass = $GLOBALS['db_pass'];
    } elseif (!empty($GLOBALS['servername']) && !empty($GLOBALS['dbname']) && array_key_exists('username', $GLOBALS) && array_key_exists('password', $GLOBALS)) {
        // Build a MySQL DSN from older config variables
        $host = $GLOBALS['servername'];
        $db   = $GLOBALS['dbname'];
        $user = $GLOBALS['username'];
        $pass = $GLOBALS['password'];

        // If servername looks like a host:port, keep it; otherwise default host
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $db);
    } else {
        throw new RuntimeException('Configuration error: please set either $dsn/$db_user/$db_pass or $servername/$dbname/$username/$password in config.php');
    }

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // Re-throw as runtime exception with a safe message
        throw new RuntimeException('PDO connection failed: ' . $e->getMessage());
    }

    return $pdo;
}

// Build PDO and run test
try {
    $pdo = buildPDOFromConfig();
} catch (RuntimeException $e) {
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit(1);
}

// Ensure logs directory exists and is writable (we'll pass it to the class)
$logPath = __DIR__ . '/logs';
if (!is_dir($logPath)) {
    @mkdir($logPath, 0755, true);
}
if (!is_writable($logPath)) {
    // Try to set permissions; if still not writable, warn but continue (class will attempt to write)
    @chmod($logPath, 0755);
}

// Instantiate the DBInsertClass (expects a PDO instance)
try {
    $instance = new DBInsertClass($pdo, $logPath);
} catch (Throwable $e) {
    echo '<p><strong>Instantiation error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit(1);
}

// Sample data to insert
$cars = [
    'Car1' => 'Mustang',
    'Car2' => 'Ecosport',
];

// Show the test data
echo '<pre>Test data:' . PHP_EOL;
print_r($cars);
echo '</pre>';

// Run the insert
$logName = 'cars_print';   // log filename prefix
$table   = 'test';         // target table name (adjust to your schema)

try {
    $result = $instance->DBInsertFunction($logName, $cars, $table);
} catch (Throwable $e) {
    // If the trait/class throws, log and show a safe message
    $errMsg = 'Insert failed: ' . $e->getMessage();
    // Attempt to write to log
    if (method_exists($instance, 'writeLog')) {
        // writeLog may be protected; attempt via reflection if necessary (best-effort)
        try {
            $ref = new ReflectionClass($instance);
            if ($ref->hasMethod('writeLog')) {
                $m = $ref->getMethod('writeLog');
                $m->setAccessible(true);
                $m->invoke($instance, $logName, $errMsg);
            }
        } catch (Throwable $ignore) {
            // ignore logging errors here
        }
    }
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($errMsg) . '</p>';
    exit(1);
}

// Output result and any session message
echo '<p>Insert result: <strong>' . htmlspecialchars((string)$result) . '</strong></p>';

if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['message'])) {
    echo '<p>Session message: ' . htmlspecialchars($_SESSION['message']) . '</p>';
}

// Also show the last few lines of the log file for quick debugging (if present)
$logFile = rtrim($logPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $logName . '.log';
if (is_readable($logFile)) {
    echo '<h3>Recent log entries</h3><pre>';
    $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        echo 'Unable to read log file.';
    } else {
        // show last 20 lines
        $tail = array_slice($lines, -20);
        foreach ($tail as $line) {
            echo htmlspecialchars($line) . PHP_EOL;
        }
    }
    echo '</pre>';
}
