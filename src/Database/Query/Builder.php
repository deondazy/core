<?php

namespace Deondazy\Core\Database\Query;

use PDOStatement;
use InvalidArgumentException;
use Deondazy\Core\Database\Connection;
use Deondazy\Core\Database\Query\AbstractBuilder;

class Builder extends AbstractBuilder
{
    /**
     * The database connection instance.
     *
     * @var Deondazy\Core\Database\Connection
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
     * @var PDOStatement
     */
    protected $statement;

    /**
     * The where clause.
     *
     * @var array
     */
    protected $where = [];

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
        $this->query = "SELECT {$columns} FROM {$this->table}";
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
            throw new InvalidArgumentException("Operator {$operator} is not supported.");
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
            $this->query .= $this->where;
        }

        $this->statement = $this->connection->prepare($this->query);
        $this->statement->execute($this->bindings);

        if ($this->statement->rowCount() > 1) {
            return $this->statement->fetchAll(\PDO::FETCH_OBJ);
        }

        return $this->statement->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Dump the SQl prepared command.
     *
     * @return string
     */
    public function dump()
    {
        ob_start();
        $this->statement->debugDumpParams();
        $output = ob_get_contents() ?: '';
        ob_end_clean();
        return '<pre>' . htmlspecialchars($output) . '</pre>';
    }
}
