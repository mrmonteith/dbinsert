<?php

// File: dbinsert.class.php - calls the dbinsert.trait.php
// Created: 4-15-2024
// By: Michael Monteith

// dbinsert.class.php
// Thin wrapper class that accepts a PDO instance and uses the trait.

// dbinsert.class.php - dependency-injection version

require_once __DIR__ . '/dbinsert.trait.php';

class DBInsertClass
{
    use DBInsertTrait;

    private PDO $pdo;
    private ?string $logPath;

    public function __construct(PDO $pdo, ?string $logPath = null)
    {
        $this->pdo = $pdo;
        $this->logPath = $logPath ?? (__DIR__ . DIRECTORY_SEPARATOR . 'logs');

        if (!is_dir($this->logPath)) {
            @mkdir($this->logPath, 0755, true);
        }
    }

    protected function getPDO(): PDO
    {
        return $this->pdo;
    }

    protected function getLogPath(): string
    {
        return $this->logPath;
    }
}
