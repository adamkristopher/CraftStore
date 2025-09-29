<?php

namespace CraftShop\Database;

use PDO;

class QueryBuilder
{
    private array $select = [];
    private ?string $from = null;
    private array $where = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $bindings = [];
    
    public function __construct(
        private PDO $connection  // Accept PDO directly
    ) {}
    
    // When executing queries:
    public function get(): array
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $stmt = $this->connection->prepare($sql);  // Use connection directly
        $stmt->execute($bindings);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->reset();
        
        return $results;
    }
    
    public function select($columns = '*'): self
    {
        // Handle various input formats
        if (is_string($columns)) {
            $columnsArray = [$columns];
        } elseif (is_array($columns)) {
            $columnsArray = $columns;
        } else {
            $columnsArray = ['*'];
        }
    
        // Validate each column name to prevent SQL injection
        foreach ($columnsArray as $column) {
            if ($column !== '*' && !preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $column)) {
                throw new \InvalidArgumentException("Invalid column name: $column");
            }
        }
    
        $this->select = $columnsArray;
        return $this;
    }
    
    public function from(string $table): self
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException("Invalid table name: $table");
        }

        $this->from = $table;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        // Validate column name to prevent injection
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: $column");
        }

        // Validate operator
        $validOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE'];
        if (!in_array(strtoupper($operator), $validOperators)) {
            throw new \InvalidArgumentException("Invalid operator: $operator");
        }

        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        if (empty($this->where)) {
            // First condition can't be OR
            return $this->where($column, $operator, $value);
        }

        // Validate column name
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: $column");
        }

        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        // Validate column
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: $column");
        }

        // Validate direction
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException("Invalid direction: $direction");
        }

        $this->orderBy[] = "`$column` $direction";

        return $this;
    }

    public function limit(int $limit): self
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException("Limit must be positive");
        }

        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException("Offset cannot be negative");
        }
        
        $this->offset = $offset;
        return $this;
    }
    
    public function toSql(): string
    {
        if (!$this->from) {
            throw new \LogicException("FROM clause is required");
        }

        $sql = 'SELECT ';

        // Build SELECT clause
        if (empty($this->select)) {
            $sql .= '*';
        } else {
            $columns = array_map(function($col) {
                return $col === '*' ? '*' : "`$col`";
            }, $this->select);
            $sql .= implode(', ', $columns);
        }
        
        // FROM clause
        $sql .= " FROM `{$this->from}`";
        
        // WHERE clause
        if (!empty($this->where)) {
            $sql .= ' WHERE ';
            $conditions = [];
            
            foreach ($this->where as $index => $condition) {
                $whereClause = "`{$condition['column']}` {$condition['operator']} ?";
                
                if ($index === 0) {
                    $conditions[] = $whereClause;
                } else {
                    $conditions[] = "{$condition['boolean']} $whereClause";
                }
            }
            
            $sql .= implode(' ', $conditions);
        }
        
        // ORDER BY clause
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        // LIMIT clause
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
            
            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }
        
        return $sql;
    }
    
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    public function reset(): self
    {
        $this->select = [];
        $this->from = null;
        $this->where = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->bindings = [];
        
        return $this;
    }
}
