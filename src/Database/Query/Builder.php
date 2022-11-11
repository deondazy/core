<?php

namespace Deondazy\Core\Database\Query;

use Deondazy\Core\Database\Connection;

class Builder
{
    /**
     * The database connection instance.
     *
     * @var \Deondazy\Core\Database\Connection
     */
    protected $connection;

    /**
     * The database table to be used.
     *
     * @var string
     */
    protected $table;

    /**
     * The SQL query string.
     *
     * @var string
     */
    protected $query;

    /**
     * The prepared PDOStatement.
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * The where clause.
     *
     * @var string
     */
    protected $where;

    /**
     * The database query bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The supported query operators.
     *
     * @var array
     */
    protected $operators = [
        '=', '!=', '<', '>', '<=', '>=', '<>', '!=',
    ];

    /**
     * Set the database connection
     *
     * @param Deondazy\Core\Database\Connection $connection
     *
     * @return $this
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Set the database table to be used.
     *
     * @param string $table
     *
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Run a select query on the database.
     *
     * @param string $columns
     *
     * @return $this
     */
    public function select($columns = '*')
    {
        $this->statement = "SELECT {$columns} FROM {$this->table}";

        return $this;
    }

    /**
     * Set the where clause.
     *
     * @param string $chainOperator
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return string $whereClause
     */
    protected function setWhereClause($chainOperator, $column, $operator, $value)
    {
        // Check if the operator is supported.
        if (!in_array($operator, $this->operators)) {
            throw new \InvalidArgumentException("Operator {$operator} is not supported.");
        }

        $whereClause = "";

        if (!empty($this->where)) {
            $whereClause = " {$chainOperator} `{$column}` {$operator} ?";
            $this->bindings[] =  $value;
        } else {
            $whereClause = " WHERE `{$column}` {$operator} ?";
            $this->bindings[] =  $value;
        }

        return $this->where .= $whereClause;
    }

    /**
     * Set the where clause for the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function where(string $column, string $operator, string $value)
    {
        $this->setWhereClause('AND', $column, $operator, $value);

        return $this;
    }

    /**
     * Chain the where clause with an OR operator.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function orWhere(string $column, string $operator, string $value)
    {
        $this->setWhereClause('OR', $column, $operator, $value);

        return $this;
    }

    /**
     * Set a raw where clause.
     *
     * @param string $whereClause
     *
     * @return $this
     */
    public function rawWhere(string $whereClause)
    {
        $this->where = " WHERE {$whereClause}";

        return $this;
    }


    /**
     * Fetch all the results from the database.
     *
     * @return array
     */
    public function get()
    {
        if (!empty($this->where)) {
            $this->statement .= $this->where;
        }

        $statement = $this->connection->prepare($this->statement);
        $statement->execute($this->bindings);

        if ($statement->rowCount() > 1) {
            return $statement->fetchAll(\PDO::FETCH_OBJ);
        }

        return $statement->fetch(\PDO::FETCH_OBJ);
    }
}
