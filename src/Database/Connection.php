<?php

namespace CraftShop\Database;

use PDO;
use PDOException;

class Connection
{
    private ?PDO $pdo = null;
    private array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the PDO instance - lazy loading pattern.
     * Only connects when actively needed.
     */
    public function getPdo(): PDO
    {
        // Singleton pattern
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    private function connect(): void
    {
        try {
            // Build DSN (Data Source Name) for MySQL
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    // Throw exceptions on errors
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Fetch associative arrays by default
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        
        } catch (PDOException $e) {
            throw new PDOException("Failed to connect to database: " . $e->getMessage());
        }
    }

    public function executeRawQuery(string $sql): array
    {
        $statement = $this->getPdo()->query($sql);
        return $statement->fetchAll();
    }
}