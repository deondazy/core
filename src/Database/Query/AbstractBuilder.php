<?php

namespace Deondazy\Core\Database\Query;

use PDOStatement;
use Deondazy\Core\Database\Connection;

abstract class AbstractBuilder
{
    /**
     * The database connection.
     *
     * @var Deondazy\Core\Database\Connection
     */
    protected Connection $connection;

    /**
     * The database table.
     *
     * @var string
     */
    protected string $table;

    /**
     * The SQL query string.
     *
     * @var string
     */
    protected string $query;

    /**
     * The prepared PDOStatement.
     *
     * @var PDOStatement
     */
    protected PDOStatement $statement;

    /**
     * The select SQL query string.
     *
     * @var string
     */
    protected $select = '';

    /**
     * The insert SQL query string.
     *
     * @var array
     */
    protected $insert = [];

    /**
     * The update SQL query string.
     *
     * @var array
     */
    protected $update = [];

    /**
     * The delete SQL query string.
     *
     * @var bool
     */
    protected $delete = false;

    /**
     * The join SQL query string.
     *
     * @var array
     */
    protected $join = [];

    /**
     * The where clause.
     *
     * @var array
     */
    protected $where = [];

    /**
     * The order by clause.
     *
     * @var string
     */
    protected $orderBy = '';

    /**
     * The group by clause.
     *
     * @var string
     */
    protected $groupBy = '';

    /**
     * The limit clause.
     *
     * @var string
     */
    protected $limit = '';

    /**
     * The offset clause.
     *
     * @var string
     */
    protected $offset = '';

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
     * Set the Database connection
     *
     * @param Connection $connection
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
}
