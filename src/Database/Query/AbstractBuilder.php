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
    private string $table;

    /**
     * The SQL query string.
     *
     * @var string
     */
    protected string $query = '';

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
        '=', '!=', '<', '>', '<=', '>=', '<>', 'LIKE', 'NOT LIKE', 'IS NULL',
        'IS NOT NULL', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'REGEXP',
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

    /**
     * Get the database table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Compose the SQL select query.
     *
     * return void
     */
    protected function composeSelect()
    {
        $this->query = "SELECT {$this->select} FROM {$this->table}";
    }

    /**
     * Compose the where clause conditions.
     *
     * return void
     */
    protected function composeWhereClauseConditions()
    {
        if (!empty($this->where)) {
            foreach ($this->where as $i => $where) {
                if (in_array($where['operator'], ['IS NULL', 'IS NOT NULL'])) {
                    $whereRaw = "{$where['column']} {$where['operator']}";
                } elseif (in_array($where['operator'], ['IN', 'NOT IN'])) {
                    $in = '';
                    $column = str_replace('.', '', $where['column']);

                    foreach ($where['value'] as $x => $item) {
                        $key = "{$column} in {$x}";
                        $in .= ":{$key}, ";
                        $this->bindings[$key] = $item;
                    }
                    $in = rtrim($in, ', ');
                    $whereRaw = "{$where['column']} {$where['operator']} ({$in})";
                } elseif (in_array($where['operator'], ['BETWEEN', 'NOT BETWEEN'])) {
                    $column = str_replace('.', '', $where['column']);
                    $whereRaw = "{$where['column']} {$where['operator']} :{$column}_btw0 AND :{$column}_btw1";
                    $this->bindings["{$column}_btw0"] = $where['value'][0];
                    $this->bindings["{$column}_btw1"] = $where['value'][1];
                } elseif ($where['clause'] === '') {
                    $whereRaw = $where['column'];
                    $this->bindings = array_merge($this->bindings, $where['value']);
                } else {
                    $column          = str_replace('.', '', $where['column']) . $i;
                    $whereRaw       = "{$where['column']} {$where['operator']} :{$column}";
                    $this->bindings[$column] = $where['value'];
                }

                if ($where['clause'] !== '') {
                    if ($i == 0) {
                        $this->query .= " WHERE {$whereRaw}";
                    } else {
                        $this->query .= " {$where['clause']} ";
                        $this->query .= $where['startParentheses'] ? '(' : '';
                        $this->query .= $whereRaw;
                        $this->query .= $where['endParentheses'] ? ')' : '';
                    }
                } else {
                    $this->query .= " {$whereRaw} ";
                }
            }
        }
    }

    /**
     * Get the raw SQL query string.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
