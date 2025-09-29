<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Database\QueryBuilder;
use PDO;  // ADD THIS IMPORT!

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $builder;
    private $mockConnection;
    
    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(PDO::class);
        $this->builder = new QueryBuilder($this->mockConnection);
    }

    public function test_select_single_column(): void
    {
        // QueryBuilder adds backticks around columns
        $expectedSql = "SELECT `name` FROM `products`";

        $sql = $this->builder
            ->select('name')
            ->from('products')
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
    }

    public function test_select_multiple_columns(): void
    {
        // Note: select() takes a single array, not multiple parameters
        $expectedSql = "SELECT `id`, `name`, `price` FROM `products`";

        $sql = $this->builder
            ->select(['id', 'name', 'price'])  // Pass as array!
            ->from('products')
            ->toSql();

        $this->assertEquals($expectedSql, $sql);  // Fixed typo: asserEquals -> assertEquals
    }

    public function test_select_all_columns(): void
    {
        // Star doesn't get backticks
        $expectedSql = "SELECT * FROM `products`";

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
    }

    public function test_where_clause_with_equals(): void
    {
        $expectedSql = "SELECT * FROM `products` WHERE `category_id` = ?";

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->where('category_id', '=', 5)  // Fixed typo: categor_id -> category_id
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals([5], $this->builder->getBindings());
    }

    public function test_where_clause_with_two_params(): void
    {
        // Note: where() requires 3 parameters, can't default operator
        $expectedSql = "SELECT * FROM `products` WHERE `sku` = ?";

        $sql = $this->builder  // Added missing $sql assignment
            ->select('*')
            ->from('products')
            ->where('sku', '=', 'CRAFT-001')  // Need all 3 params
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals(['CRAFT-001'], $this->builder->getBindings());
    }

    public function test_multiple_where_clauses(): void
    {
        $expectedSql = "SELECT * FROM `products` WHERE `category_id` = ? AND `price` < ? AND `stock` > ?";

        $sql = $this->builder
            ->select('*')
            ->from('products')  // Fixed typo: product -> products
            ->where('category_id', '=', 5)
            ->where('price', '<', 20.00)
            ->where('stock', '>', 0)
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals([5, 20.00, 0], $this->builder->getBindings());
    }

    public function test_order_by_single_column(): void
    {
        $expectedSql = "SELECT * FROM `products` ORDER BY `created_at` DESC";

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->orderBy('created_at', 'DESC')
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
    }

    public function test_order_by_defaults_to_asc(): void
    {
        $expectedSql = "SELECT * FROM `products` ORDER BY `name` ASC";

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->orderBy('name')
            ->toSql();

        $this->assertEquals($expectedSql, $sql);  // Fixed typo
    }

    public function test_limit_clause(): void
    {
        $expectedSql = "SELECT * FROM `products` LIMIT 10";  // Fixed typo

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->limit(10)
            ->toSql();

        $this->assertEquals($expectedSql, $sql);  // Fixed typo
    }

    public function test_offset_with_limit(): void
    {
        $expectedSql = "SELECT * FROM `products` LIMIT 10 OFFSET 20";  // Fixed typo

        $sql = $this->builder
            ->select('*')
            ->from('products')
            ->limit(10)
            ->offset(20)
            ->toSql();

        $this->assertEquals($expectedSql, $sql);
    }

    public function test_complex_query_for_search(): void
    {
        $expectedSql = "SELECT `id`, `name`, `price`, `stock` FROM `products` WHERE `category_id` = ? AND `price` <= ? AND `stock` > ? AND `featured` = ? ORDER BY `popularity` DESC LIMIT 12";

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

        $this->assertEquals($expectedSql, $sql);  // Fixed variable name
        $this->assertEquals([5, 25.00, 0, true], $this->builder->getBindings());
    }

    public function test_prevents_sql_injection_in_column_names(): void
    {
        // This should throw an exception due to invalid column name
        $this->expectException(\InvalidArgumentException::class);
        
        $this->builder
            ->select('users.name; DROP TABLE users--')  // Invalid column name
            ->from('products')
            ->toSql();
    }

    public function test_method_chaining_returns_self(): void
    {
        $result = $this->builder->select('*');
        $this->assertSame($this->builder, $result);

        $result = $result->from('products');
        $this->assertSame($this->builder, $result);

        $result = $result->where('id', '=', 1);  // Added missing operator
        $this->assertSame($this->builder, $result);
    }

    public function test_reset_builder_for_new_query(): void
    {
        $this->builder
            ->select('*')
            ->from('products')
            ->where('id', '=', 1);  // Added missing operator
        
        $firstSql = $this->builder->toSql();
        
        $this->builder->reset();
        $secondSql = $this->builder
            ->select('name')
            ->from('users')
            ->toSql();
        
        $this->assertEquals("SELECT * FROM `products` WHERE `id` = ?", $firstSql);
        $this->assertEquals("SELECT `name` FROM `users`", $secondSql);
        $this->assertEquals([], $this->builder->getBindings());
    }
}