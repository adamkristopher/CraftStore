<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Database\QueryBuilder;
use CraftShop\Database\Connection;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $builder;
    private $connectionMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Connection::class);
        $this->$builder = new QueryBuilder($this->connectionMock);
    }

    public function test_select_single_column(): void
    {
        // Arrange
        $expectedSql = "SELECT name FROM products";

        // Act
        $sql = $this->builder
            ->select('name')
            ->from('products')
            ->toSql();

        // Assert
        $this->assertEquals($expectedSql, $sql);
    }

    public function test_select_multiple_columns(): void
    {
        // Arrange
        $expectedSql = "SELECT id, name, price FROM products";

        // Act
        $sql = $this->builder
            ->select('id', 'name', 'price')
            ->from('products')
            ->toSql();

        // Assert
        $this->asserEquals($expectedSql, $sql);
    }

    public function test_select_all_columns(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM products";

        // Act
        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->toSql();

        // Assert
        $this->assertEquals($expectedSql, $sql);
    }

    public function test_where_clause_with_equals(): void
    {
        // Assert
        $expectedSql = "SELECT * FROM products WHERE category_id = ?";

        // Act
        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->where('categor_id', '=', 5)
            ->toSql();

        // Assert
        $this->assertEquals($expectedSql, $sql);
    }

    public function test_where_clause_defaults_to_equals():void
    {
        // Arrange
        $expectedSql = "SELECT * FROM products WHERE sku = ?";

        // Act
        $this->builder
            ->select('*')
            ->from('products')
            ->where('sku', 'CRAFT-001')
            ->toSql();

        // Assert
        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals(['CRAFT-001'], $this->builder->getBindings());
    }

    public function test_multiple_where_clauses(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM products WHERE category_id = ? AND price < ? AND stock > ?";

        // Act
        $sql = $this->builder
            ->select('*')
            ->from('product')
            ->where('category_id', '=', 5)
            ->where('price', '<', 20.00)
            ->where('stock', '>', 0)
            ->toSql();

        // Assert
        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals([5, 20.00, 0], $this->builder->getBindings());
    }

    public function test_order_by_single_column(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM products ORDER BY created_at DESC";

        // Act
        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->orderBy('created_at', 'DESC')
            ->toSql();

        // Assert
        $this->assertEquals($expectedSql, $sql);
    }

    public function test_order_by_defaults_to_asc(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM products ORDER BY name ASC";

        // Act
        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->orderBy('name')
            ->toSql();

        // Assert
        $this->asserEquals($expectedSql, $sql);
    }

    public function test_limit_clause(): void
    {
        $excpectedSql = "SELECT * FROM products LIMIT 10";

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->limit(10)
            ->toSql();

        $this->asserEquals($excpectedSql, $sql);
    }

    public function test_offset_with_limit(): void
    {
        // For pagination
        $excpectedSql = "SELECT * FROM products LIMIT 10 OFFSET 20";

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->limit(10)
            ->offset(20)
            ->toSql();

        $this->assertEquals($excpectedSql, $sql);
    }

    public function test_complex_query_for_search(): void
    {
        // Real world query: find featured papers under $25, in stock.
        $expectedSql = "SELECT id, name, price, stock FROM products WHERE category_id = ? AND price <= ? AND stock > ? AND featured = ? ORDER BY popularity DESC LIMIT 12";

        $sql = $this->builder
            ->select(['id', 'name', 'price', 'stock'])
            ->from('products')
            ->where('category_id', '=', 5)
            ->where('price', '<=', 25.00)
            ->where('stock', '>', 0)
            ->where('featured', '=', true)
            ->orderBy('popularity', 'DESC')
            ->limit(12)
            ->toSql();

        $this->assertEquals($excpectedSql, $sql);
        $this->assertEquals([5, 25.00, 0, true], $this->builder->getBindings());
    }

    public function test_prevents_sql_injection_in_column_names(): void
    {
        // Security test: malicious column name should be quoted.
        $excpectedSql = "SELECT `users`.`name; DROP TABLE users--` FROM products";

        $sql = $this->builder
        ->select('users.name; DROP TABLE users--')  // Malicious input
        ->from('products')
        ->toSql();

        $this->assertEquals($expectedSql, $sql);
    }

    public function test_method_chaining_returns_self(): void
    {
        // Fluent interface test

        // Assert each method returns the builder for chaining
        $result = $this->builder->select('*');
        $this->assertSame($this->builder, $result);

        $result = $result->from('products');
        $this->assertSame($this->builder, $result);

        $result = $result->where('id', 1);
        $this->assertSame($this->builder, $result);
    }

    public function test_reset_builder_for_new_query(): void
    {
        // Arrange - build first query
        $this->builder
            ->select('*')
            ->from('products')
            ->where('id', 1);
        
        $firstSql = $this->builder->toSql();
        
        // Act - reset and build new query
        $this->builder->reset();
        $secondSql = $this->builder
            ->select('name')
            ->from('users')
            ->toSql();
        
        // Assert
        $this->assertEquals("SELECT * FROM products WHERE id = ?", $firstSql);
        $this->assertEquals("SELECT name FROM users", $secondSql);
        $this->assertEquals([], $this->builder->getBindings()); // Bindings cleared
    }
}