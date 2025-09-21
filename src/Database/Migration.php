<?php

namespace CraftShop\Database;

/**
 * Database Migration System
 * 
 * This is how modern PHP applications manage database changes.
 * Instead of random SQL files, we track what's been run.
 */
class Migration
{
    private Connection $connection;
    
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Create the migrations tracking table
     * This table records which migrations have been run
     */
    public function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_migration_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        $this->connection->executeRawQuery($sql);
    }
    
    /**
     * Run a migration if it hasn't been run before
     * 
     * @param string $name Unique name for this migration
     * @param callable $callback Function that performs the migration
     */
    public function run(string $name, callable $callback): void
    {
        // Make sure migrations table exists
        $this->createMigrationsTable();
        
        // Check if this migration has already been run
        if ($this->hasRun($name)) {
            return; // Skip if already executed
        }
        
        $pdo = $this->connection->getPdo();
        $transactionStarted = false;
        
        try {
            // Try to start transaction
            // Note: DDL statements (CREATE/ALTER/DROP TABLE) auto-commit in MySQL
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
                $transactionStarted = true;
            }
            
            // Execute the migration
            $callback($this->connection);
            
            // Record that we ran this migration
            $this->recordMigration($name);
            
            // Commit if we started a transaction and it's still active
            if ($transactionStarted && $pdo->inTransaction()) {
                $pdo->commit();
            }
            
        } catch (\Exception $e) {
            // Rollback if transaction is still active
            if ($transactionStarted && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new \RuntimeException(
                "Migration '$name' failed: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Check if a migration has already been run
     */
    private function hasRun(string $name): bool
    {
        $sql = "SELECT COUNT(*) as count FROM migrations WHERE name = :name";
        
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Record that a migration has been executed
     */
    private function recordMigration(string $name): void
    {
        $sql = "INSERT INTO migrations (name) VALUES (:name)";
        
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $name]);
    }
    
    /**
     * Rollback a specific migration
     * In production, you'd use this carefully!
     */
    public function rollback(string $name, callable $downCallback): void
    {
        if (!$this->hasRun($name)) {
            return; // Can't rollback what hasn't been run
        }
        
        $pdo = $this->connection->getPdo();
        $transactionStarted = false;
        
        try {
            // Try to start transaction (DDL might auto-commit)
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
                $transactionStarted = true;
            }
            
            // Run the rollback
            $downCallback($this->connection);
            
            // Remove from migrations table
            $sql = "DELETE FROM migrations WHERE name = :name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name]);
            
            // Commit if transaction is still active
            if ($transactionStarted && $pdo->inTransaction()) {
                $pdo->commit();
            }
            
        } catch (\Exception $e) {
            if ($transactionStarted && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new \RuntimeException(
                "Rollback of '$name' failed: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Get list of all migrations that have been run
     * Useful for debugging
     */
    public function getExecutedMigrations(): array
    {
        $this->createMigrationsTable();
        
        return $this->connection->executeRawQuery(
            "SELECT * FROM migrations ORDER BY executed_at ASC"
        );
    }
}
