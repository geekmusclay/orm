<?php

declare(strict_types=1);

namespace Geekmusclay\ORM;

use Exception;

use function array_merge;
use function count;
use function strpos;
use function strtoupper;
use function trim;

class QueryBuilder
{
    /** @var string[] $selects The properties to be selected in the query */
    private array $selects = [];

    /** @var string|null $insert The name of the table where the data should be inserted  */
    private ?string $insert = null;

    /** @var array<string, string> $values Table of values to be inserted */
    private array $values = [];

    /** @var string|null $update The name of the table that will be updated */
    private ?string $update = null;

    /** @var string|null $from Table targted by the query */
    private ?string $from = null;

    /** @var string[] $joins Joints to be made */
    private array $joins = [];

    /** @var string[] $wheres Conditions to get results from the query */
    private array $wheres = [];

    /** @var string|null $order The property or properties that will order the result of the query */
    private ?string $order = null;

    /** @var string $direction The direction of the order */
    private string $direction = 'ASC';

    /** @var int|null $limit Result limit for the query */
    private ?int $limit = null;

    /**
     * Defines the properties to be selected in the query.
     *
     * @param string|string[] $target Properties to select
     */
    public function select($target): self
    {
        // phpcs:disable
        if (is_string($target)) {
            $this->selects[] = $target;
        } elseif (is_array($target)) {
            $this->selects = array_merge($this->selects, $target);
        } else {
            throw new Exception('Wrong type');
        }
        // phpcs:enable

        return $this;
    }

    /**
     * Defines the table where the data should be inserted
     *
     * @param string      $table Targeted table
     * @param string|null $name  The name of the table
     */
    public function insertInto(string $table, ?string $name = null): self
    {
        if (null !== $name) {
            $table .= ' AS ' . $name;
        }
        $this->insert = $table;

        return $this;
    }

    /**
     * Defines the table where the data should be updated
     *
     * @param string      $table Targeted table
     * @param string|null $name  The name of the table
     */
    public function update(string $table, ?string $name = null): self
    {
        if (null !== $name) {
            $table .= ' AS ' . $name;
        }
        $this->update = $table;

        return $this;
    }

    /**
     * Defines the table targeted by the query.
     *
     * @param string $target Targeted table
     * @param string $name   Name of the table
     */
    public function from(string $target, ?string $name = null): self
    {
        if (null !== $name) {
            $target .= ' AS ' . $name;
        }
        $this->from = $target;

        return $this;
    }

    /**
     * Add a table join. By defautl an inner join.
     *
     * @param string               $table      Table to join
     * @param array<array<string>> $conditions The ON conditions for the join
     * @param string|null          $name       The name of the joinned table
     * @param string               $type       Type of join
     */
    public function join(
        string $table,
        array $conditions,
        ?string $name = null,
        string $type = 'INNER'
    ): self {
        if (0 === count($conditions)) {
            throw new Exception('No conditions');
        }

        $first = true;
        $join  = strtoupper($type) . ' JOIN ' . $table;
        if (null !== $name) {
            $join .= ' AS ' . $name;
        }

        foreach ($conditions as $condition) {
            if (3 !== count($condition)) {
                continue;
            }

            if (true === $first) {
                $join .= ' ON ';
            } else {
                $join .= ' AND ';
            }
            $join .= $condition[0] . ' ' . $condition[1] . ' ' . $condition[2];
            $first = false;
        }
        $this->joins[] = $join;

        return $this;
    }

    /**
     * Add values for insert or update queries
     *
     * @param array<string, string> $values The values to insert or update
     */
    public function values(array $values): self
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    /**
     * Define conditions to get results from the query.
     *
     * @param array<string, mixed> $condtions Conditions to get results
     */
    public function where(array $condtions): self
    {
        $this->wheres = array_merge($this->wheres, $condtions);

        return $this;
    }

    /**
     * Defines the property or properties that will order the result of the query.
     *
     * @param string $property  The property(ies) for the order
     * @param string $direction The direction of the order
     */
    public function orderBy(string $property, string $direction = 'ASC'): self
    {
        $this->order     = $property;
        $this->direction = $direction;

        return $this;
    }

    /**
     * Define the result limit for the query.
     *
     * @param integer $limit The result limit
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the final result.
     *
     * @return string Properly formatted query
     */
    public function getQuery(): string
    {
        if (0 !== count($this->selects)) {
            return $this->buildSelectQuery();
        }

        if (null !== $this->insert) {
            return $this->buildInsertQuery();
        }

        if (null !== $this->update) {
            return $this->buildUpdateQuery();
        }

        throw new Exception('Wrong query construction.');
    }

    /**
     * Will build the select query
     *
     * @return string The query built
     */
    private function buildSelectQuery(): string
    {
        $query = 'SELECT ';

        $count = count($this->selects);
        for ($i = 0; $i < $count; $i++) {
            if ($i === $count - 1) {
                $query .= $this->selects[$i] . ' ';
            } else {
                $query .= $this->selects[$i] . ', ';
            }
        }

        if (null === $this->from) {
            throw new Exception('No target table set');
        }
        $query .= 'FROM ' . $this->from . ' ';

        if (0 !== count($this->joins)) {
            foreach ($this->joins as $join) {
                $query .= $join . ' ';
            }
        }

        $query .= $this->buildWhereCondition();

        if (null !== $this->order) {
            $query .= 'ORDER BY ' . $this->order . ' ' . $this->direction . ' ';
        }

        if (null !== $this->limit) {
            $query .= 'LIMIT ' . $this->limit;
        }

        return trim($query);
    }

    /**
     * Will build the insert query
     *
     * @return string The query built
     */
    private function buildInsertQuery(): string
    {
        $query = 'INSERT INTO ' . $this->insert;

        $properties = '(';
        $values     = '(';
        $index      = 1;
        $total      = count($this->values);
        foreach ($this->values as $property => $value) {
            if ('?' === $value || false !== strpos((string) $value, ':')) {
                $values .= $value;
            } else {
                $values .= '"' . $value . '"';
            }
            $properties .= $property;

            if ($index !== $total) {
                $properties .= ', ';
                $values     .= ', ';
            }
            ++$index;
        }
        $properties .= ')';
        $values     .= ')';

        $query .= ' ' . $properties . ' VALUES ' . $values;

        return trim($query);
    }

    /**
     * Will build the update query
     *
     * @return string The query built
     */
    private function buildUpdateQuery(): string
    {
        $query = 'UPDATE ' . $this->update . ' ';

        if (null !== $this->from) {
            $query .= 'FROM ' . $this->from . ' ';
        }

        if (0 !== count($this->joins)) {
            foreach ($this->joins as $join) {
                $query .= $join . ' ';
            }
        }
        $query .= 'SET ';

        $index = 1;
        $total = count($this->values);
        foreach ($this->values as $property => $value) {
            if ('?' === $value || false !== strpos((string) $value, ':')) {
                $query .= $property . ' = ' . $value;
            } else {
                $query .= $property . ' = "' . $value . '"';
            }

            if ($index !== $total) {
                $query .= ', ';
            } else {
                $query .= ' ';
            }
            ++$index;
        }

        $query .= $this->buildWhereCondition();

        return trim($query);
    }

    /**
     * Will build the where condition for query
     *
     * @return string The where condition built
     */
    private function buildWhereCondition(): ?string
    {
        if (0 === count($this->wheres)) {
            return null;
        }

        $where = 'WHERE ';
        foreach ($this->wheres as $property => $value) {
            if ($where === 'WHERE ') {
                $where .= $property . ' = ' . $value . ' ';
            } else {
                $where .= 'AND ' . $property . ' = ' . $value . ' ';
            }
        }

        return $where;
    }

    /**
     * Flush all stored data
     */
    public function flush(): self
    {
        $this->selects   = [];
        $this->insert    = null;
        $this->values    = [];
        $this->update    = null;
        $this->from      = null;
        $this->joins     = [];
        $this->wheres    = [];
        $this->order     = null;
        $this->direction = 'ASC';
        $this->limit     = null;

        return $this;
    }
}
