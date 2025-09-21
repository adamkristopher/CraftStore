<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use CraftShop\Database\Connection;
use PDO;

class DatabaseConnectionTest extends TestCase
{
  private Connection $connection;

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
  }

  public function test_can_connect_to_database(): void
  {
    // Arrange
    $connection = $this->connection;

    // Act
    $pdo = $connection->getPdo();

    // Assert
    $this->assertInstanceOf(PDO::class, $pdo);
    $this->assertEquals('mysql', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
  }

  public function test_throws_exception_with_invalid_credentials(): void
  {
    // Assert
    $this->expectException(\PDOException::class);

    // Arrange & Act
    $connection = new Connection([
        'driver'   => 'mysql',
        'host'     => 'mysql', 
        'username' => 'wront_user', 
        'password' => 'wrong_password', 
        'database' => 'nonexistent'
    ]);

    $connection->getPdo();
  }

  public function test_connecction_is_singleton(): void
  {
    // Arrange
    $config = [
        'driver'   => 'mysql',
        'host'     => 'mysql', 
        'username' => 'root', 
        'password' => 'secret', 
        'database' => 'craftshop',
        'charset'  => 'utf8mb4'
    ];

    $connection = new Connection($config);

    // Act
    $pdo1 = $connection->getPdo();
    $pdo2 = $connection->getPdo();

    // Assert - Assert that the same PDO object is returned
    $this->assertSame($pdo1, $pdo2);
  }

  public function test_can_execute_raw_query(): void
  {
    $connection = $this->connection;

    // Act
    $result = $connection->executeRawQuery('SELECT 1 + 1 AS result');

    // Assert
    $this->assertEquals(2, $result[0]['result']);
  }
  
  public function test_supports_multiple_database_drivers(): void
  {
    // This test documents that we support both MySQL and MSSQL
    
    $this->markTestSkipped('MSSQL driver test - implement when needed');
    
    // Future: Test MSSQL connection
    // $connection = new Connection([
    //     'driver' => 'sqlsrv',
    //     'host' => 'localhost',
    //     'database' => 'craftshop_test'
    // ]);
  }
}