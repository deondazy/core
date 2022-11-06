<?php

namespace Deondazy\Core;

use Deondazy\Core\Exceptions\DatabaseException;
use PDO;
use PDOStatement;
use PDOException;

class Database
{
    /**
     * The data source name.
     *
     * @var string
     */
    private $dsn;

    /**
     * The database username.
     *
     * @var string
     */
    private $username;

    /**
     * The database password.
     *
     * @var string
     */
    private $password;

    /**
     * The active PDO connection.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * The prepared PDOStatement.
     *
     * @var PDOStatement
     */
    private $statement;

    /**
     * The last Query string.
     *
     * @var string
     */
    private $lastQuery;

    /**
     * The last Query data.
     *
     * @var array
     */
    private $lastData;

    /**
     * The last WHERE clause data.
     *
     * @var array
     */
    private $lastWhereData;

    /**
     * Connection options.
     *
     * @var array
     */
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Disable multi query execution
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    ];

    /**
     * Set up the database connection credentials.
     *
     * @param string $dsn The data source name.
     * @param string $username The database username.
     * @param string $password The database password.
     *
     * @return void
     */
    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null
    ) {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Connect to the Database.
     *
     * Sets connection options, and connect to the Database
     *
     * @param string $dsn Data Source Name for the database driver
     * @param string $username Database username
     * @param string $password Database password
     *
     * @return PDO
     */
    public function connect(): PDO
    {
        // Return early if already connected
        if ($this->isConnected()) {
            return $this->pdo;
        }

        // If passed DSN is an instance of PDO, use it directly
        if ($this->dsn instanceof PDO) {
            $this->pdo = $this->dsn;
            return $this->pdo;
        }

        $options = $this->options;

        try {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }

        return $this->pdo;
    }

    /**
     * Checks if there's an active Database connection.
     *
     * @return bool
     */
    public function isConnected()
    {
        return isset($this->pdo);
    }

    /**
     * Close database connection (Clean up).
     */
    public function close()
    {
        $this->pdo       = null;
        $this->statement = null;
    }

    /**
     * Prepare a query to run against the database.
     *
     * @param string $query
     *
     * @throws DatabaseException
     *
     * @return PDOStatement
     */
    public function query($query)
    {
        try {
            $this->statement = $this->pdo->prepare($query);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
        return $this->statement;
    }

    /**
     * Bind the inputs with the query placeholders.
     *
     * @param string $param
     * @param string $value
     * @param string $type
     *
     * @return bool
     */
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;

                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;

                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;

                default:
                    $type = PDO::PARAM_STR;
            }
        }

        return $this->statement->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement.
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function execute()
    {
        try {
            if ($this->statement instanceof PDOStatement) {
                return $this->statement->execute();
            }
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }

        return false;
    }

    /**
     * The WHERE clause.
     *
     * @param array $where
     *
     * @return string
     */
    private function whereClause($where)
    {
        $whereClause = '';
        if (!empty($where)) {
            $whereClause = ' WHERE ';
            $whereClause .= implode(' AND ', array_map(function ($key) {
                return $key . ' = :' . $key;
            }, array_keys($where)));
        }
        return $whereClause;
    }

    /**
     * Bind the data inputs with the query placeholders.
     *
     * @param array $bindData
     *
     * @return void
     */
    private function bindData($bindData)
    {
        if (empty($bindData)) {
            return;
        }

        foreach ($bindData as $key => $value) {
            $this->bind(':' . $key, $value);
        }
    }

    /**
     * Run a raw query against the database.
     *
     * @param string $query
     *
     * @return Database
     */
    public function rawQuery($query)
    {
        $this->lastQuery = $query;
        return $this;
    }

    /**
     * Insert a row into the database.
     *
     * @param string $table The table name
     * @param array $data The data to insert
     *
     * @return int The last inserted ID
     */
    public function insert($table, array $data)
    {
        $col   = '';
        $val   = '';

        foreach ($data as $column => $value) {
            $col .= "`{$column}`, ";
            $val .= ":{$column}, "; // use the column names as named parameter
        }

        $column = rtrim($col, ', '); // Remove last comma(,) on column names
        $value = rtrim($val, ', '); // Remove last comma(,) on named parameters

        // Construct the query
        $this->query("INSERT INTO {$table} ({$column}) VALUES ({$value})");

        //Bind all parameters
        foreach ($data as $param => $value) {
            $this->bind(":{$param}", $value);
        }

        $this->execute();

        return $this->lastInsertId();
    }

    /**
     * Update a row in the database.
     *
     * Return partial last query so we can use it in the next query
     *
     * @param string $table The table name
     *
     * @return Database
     */
    public function update($table)
    {
        $this->lastQuery = "UPDATE {$table}";
        return $this;
    }

    /**
     * Set the data to update.
     *
     * @param array $data The data to update
     *
     * @return Database
     */
    public function set(array $data)
    {
        $setClause = ' SET ';

        // Construct the SET part of the query
        foreach ($data as $column => $value) {
            $setClause .= "{$column} = :{$column}, ";
        }

        // Remove the last comma(,) from the SET part of the query
        $setClause = rtrim($setClause, ', ');

        $this->lastQuery .= $setClause;
        $this->lastData = $data;

        return $this;
    }

    /**
     * Set the where clause.
     *
     * @param array $where The where clause
     *
     * @return Database
     */
    public function where(array $where)
    {
        $this->lastQuery .= $this->whereClause($where);
        $this->lastWhereData = $where;
        return $this;
    }

    /**
     * ORDER BY clause.
     *
     * @param string $column The column name
     * @param string $order The order type
     *
     * @return Database
     */
    public function orderBy($column, $order = 'ASC')
    {
        $this->lastQuery .= " ORDER BY {$column} {$order}";
        return $this;
    }

    /**
     * LIMIT clause.
     *
     * @param int $limit The limit
     *
     * @return Database
     */
    public function limit($limit)
    {
        $this->lastQuery .= " LIMIT {$limit}";
        return $this;
    }

    /**
     * OFFSET clause.
     *
     * @param int $offset The offset
     *
     * @return Database
     */
    public function offset($offset)
    {
        $this->lastQuery .= " OFFSET {$offset}";
        return $this;
    }

    /**
     * Execute the last query.
     *
     * @param array $data The data to update
     *
     * @return int The number of affected rows
     */
    public function run()
    {
        // Prepare the query
        $this->query($this->lastQuery);

        // Bind the data parameters
        $this->bindData($this->lastData);

        // Bind the where clause parameter
        $this->bindData($this->lastWhereData);

        // Execute the query
        $this->execute();

        return $this->rowCount();
    }

    /**
     * Delete Query
     *
     * Return partial last query so we can use it in the next query
     *
     * @param string $table Table to update
     *
     * @return Database
     */
    public function delete($table)
    {
        $this->lastQuery = "DELETE FROM {$table}";
        return $this;
    }

    /**
     * Get a single row from the database.
     *
     * Return partial last query so we can use it in the next query
     *
     * @param string $table Table to run the query on
     *
     * @return Database
     */
    public function select($table)
    {
        $this->lastQuery = "SELECT * FROM {$table}";
        return $this;
    }

    /**
     * Fetch all rows from the database.
     *
     * @return array
     */
    public function fetchAll()
    {
        // Prepare the query
        $this->query($this->lastQuery);

        // Bind the where clause parameter
        $this->bindData($this->lastWhereData);

        // Execute the query
        $this->execute();

        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch a single row from the database.
     *
     * @return object
     */
    public function fetchOne()
    {
        // Prepare the query
        $this->query($this->lastQuery);

        // Bind the where clause parameter
        $this->bindData($this->lastWhereData);

        // Execute the query
        $this->execute();

        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Count the affected rows returned by the last query.
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->statement->rowCount();
    }

    /**
     * The last AUTO_INCREMENTed id for the last INSERT query.
     *
     * @return int
     */
    public function lastInsertId()
    {
        return (int) $this->pdo->lastInsertId();
    }
}
