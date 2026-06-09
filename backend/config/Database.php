<?php

/**
 * Connessione PDO — Dependency Injection (no Singleton).
 */
class Database
{
    public function __construct(
        private string $host,
        private string $dbname,
        private string $user,
        private string $pass
    ) {}

    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        return new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
