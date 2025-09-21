<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Database\Migration;
use CraftShop\Database\Connection;

class MigrationTest extends TestCase
{
  private Connection $connection;
  private Migration $migration;

  protected function setUp(): void
  {
    $this->connection = new Connection([
      'driver'   => 'mysql',
      'host'     => 'mysql', 
      'username' => 'root', 
      'password' => 'secret', 
      'database' => 'craftshop',
      'charset'  => 'utf8mb4'
    ]);

    $this->migration = new Migration($this->connection);
  }

  public function test_can_create_migration(): void
  {
    $this->migration->createMigrationsTable();

    $tables = $this->connection->executeRawQuery(
        "SHOW TABLES LIKE 'migrations'"
    );

    $this->assertNotEmpty($tables, 'Migrations table should exist');
  }

  public function test_can_run_a_migration(): void
  {
    // Arrange
    $this->migration->createMigrationsTable();

    // Act - Run a test migration to create products table
    $this->migration->run('create_products_table', function($connection) {
        $connection->executeRawQuery("
            CREATE TABLE products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sku VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                stock_quantity INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    });

    // Assert - Products table should exist
    $tables = $this->connection->executeRawQuery(
        "SHOW TABLES LIKE 'products'"
    );
    $this->assertNotEmpty($tables);
    
    // Assert - Migration should be recorded
    $recorded = $this->connection->executeRawQuery(
        "SELECT * FROM migrations WHERE name = 'create_products_table'"
    );
    $this->assertNotEmpty($recorded);
  }

  public function test_can_get_list_of_executed_migrations(): void 
  {
      // Arrange - Run some migrations first
      $this->migration->createMigrationsTable();
      
      $this->migration->run('first_migration', function($connection) {
          // Empty migration - just for testing
      });
      
      $this->migration->run('second_migration', function($connection) {
          // Another empty migration
      });
      
      // Act - Get the list of executed migrations
      $executedMigrations = $this->migration->getExecutedMigrations();
      
      // Assert - Should have 2 migrations
      $this->assertCount(2, $executedMigrations);
      
      // Assert - Check migration names
      $migrationNames = array_column($executedMigrations, 'name');
      $this->assertContains('first_migration', $migrationNames);
      $this->assertContains('second_migration', $migrationNames);
      
      // Assert - Each migration should have required fields
      foreach ($executedMigrations as $migration) {
          $this->assertArrayHasKey('id', $migration);
          $this->assertArrayHasKey('name', $migration);
          $this->assertArrayHasKey('executed_at', $migration);
      }
  }

    public function test_can_rollback_an_executed_migration(): void
    {
        // ARRANGE - Run a migration first
        $this->migration->run('test_migration', function($conn) {
            $conn->executeRawQuery("CREATE TABLE test_table (id INT PRIMARY KEY)");
        });

        // Verify it was created
        $tables = $this->connection->executeRawQuery("SHOW TABLES LIKE 'test_table'");
        $this->assertNotEmpty($tables);

        // ACT - Rollback the migration
        $this->migration->rollback('test_migration', function($conn) {
            $conn->executeRawQuery("DROP TABLE test_table");
        });

        // ASSERT - Table should be gone
        $tables = $this->connection->executeRawQuery("SHOW TABLES LIKE 'test_table'");
        $this->assertEmpty($tables);

        // ASSERT - Migration record should be gone
        $migrations = $this->connection->executeRawQuery(
            "SELECT * FROM migrations WHERE name = 'test_migration'"
        );
        $this->assertEmpty($migrations);
    }

  public function test_does_not_run_migration_twice(): void
  {
    // Arrange
    $this->migration->createMigrationsTable();
    $runCount = 0;

    // Act - Try to run same migration twice
    $this->migration->run('test_migration', function() use (&$runCount) {
        $runCount++;
    });

    $this->migration->run('test_migration', function() use (&$runCount) {
        $runCount++;
    });

    // Assert - Should only run once
    $this->assertEquals(1, $runCount);
  }
  
  protected function tearDown(): void
  {
      // Clean up test tables
      try {
          $this->connection->executeRawQuery("DROP TABLE IF EXISTS products");
          $this->connection->executeRawQuery("DROP TABLE IF EXISTS migrations");
          $this->connection->executeRawQuery("DROP TABLE IF EXISTS test_table");
      } catch (\Exception $e) {
          // Ignore cleanup errors
      }
  }
}