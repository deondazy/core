<?php

namespace Deondazy\Core\Database;

use PDO;
use PDOStatement;
use BadMethodCallException;
use Deondazy\Core\Database\ConnectionInterface;

abstract class AbstractConnection extends PDO implements ConnectionInterface
{
    /**
     * The database connection.
     *
     * @var PDO|null
     */
    protected ?PDO $connection = null;

    /**
     * Proxies the PDO methods for specific database drivers.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments)
    {
        $this->connect();

        if (method_exists($this->connection, $method)) {
            return call_user_func_array([$this->connection, $method], $arguments);
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Connect to the database.
     *
     * @return void
     */
    abstract public function connect();

    /**
     * Disconnect from the database.
     *
     * @return void
     */
    abstract public function disconnect();

    /**
     * Get the database connection.
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Is the database connected?
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connection instanceof PDO;
    }

    /**
     * Run a query after preparing it with the bound values and return a PDOStatement.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return PDOStatement
     */
    public function runQuery(string $statement, array $values = [])
    {
        $this->connect();
        $query = $this->prepareQueryWithValues($statement, $values);
        $query->execute();
        return $query;
    }

    /**
     * Prepare an SQL statement.
     *
     * @param string $statement
     * @param array $driverOptions
     *
     * @return PDOStatement
     */
    public function prepare($statement, $driverOptions = [])
    {
        $this->connect();
        return $this->connection->prepare($statement, $driverOptions);
    }

    /**
     * Prepare an SQL statement with bound values and return a PDOStatement.
     *
     * Used for binding values with placeholders (also question-marked placeholders)
     * in the SQL statement.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return PDOStatement
     */
    public function prepareQueryWithValues(string $statement, array $values = [])
    {
        if (empty($values)) {
            return $this->prepare($statement);
        }

        $this->connect();
        $query = $this->connection->prepare($statement);
        foreach ($values as $key => $value) {
            $this->bindValue($query, $key, $value);
        }
        return $query;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * @param string $statement
     * @param int|null $fetchMode
     * @param mixed|null $fetchArgument
     *
     * @return PDOStatement|false
     */
    public function query(string $statement, int $fetchMode = null, mixed ...$fetchArgument)
    {
        $this->connect();
        return $this->connection->query($statement, $fetchMode, ...$fetchArgument);
    }

    /**
     * Quotes a string for use in a query.
     *
     * This differs from `PDO::quote()` in that it will convert an array into
     * a string of comma-separated quoted values.
     *
     * @param mixed $value
     * @param int $parameterType
     *
     * @return string|false
     */
    public function quote($value, $parameterType = PDO::PARAM_STR)
    {
        $this->connect();

        $value = $value ?? '';

        // non-array quoting
        if (! is_array($value)) {
            return $this->connection->quote($value, $parameterType);
        }

        // quote array values, not keys, then combine with commas
        foreach ($value as $k => $v) {
            $value[$k] = $this->connection->quote($v, $parameterType);
        }
        return implode(', ', $value);
    }

    /**
     * Run a query and return the number of affected rows.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return int
     */
    public function fetchAffectedRows(string $statement, array $values = [])
    {
        $query = $this->runQuery($statement, $values);
        return $query->rowCount();
    }

    /**
     * Fetch one row from the result set as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array|null
     */
    public function fetchOne(string $statement, array $values = [])
    {
        $query = $this->runQuery($statement, $values);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows from the result set as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAll(string $statement, array $values = [])
    {
        $query = $this->runQuery($statement, $values);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Bind a value to a parameter.
     *
     * @param PDOStatement $query
     * @param mixed   $key
     * @param mixed   $value
     *
     * @return bool
     */
    protected function bindValue(PDOStatement $query, $key, $value)
    {
        switch ($value) {
            case is_int($value):
                $type = self::PARAM_INT;
                break;

            case is_bool($value):
                $type = self::PARAM_BOOL;
                break;

            case is_null($value):
                $type = self::PARAM_NULL;
                break;

            default:
                $type = self::PARAM_STR;
        }

        return $query->bindValue($key, $value, $type);
    }

    /**
     * Return the ID of the last inserted row or sequence value.
     *
     * @param string|null $name
     *
     * @return string|bool
     */
    public function lastInsertId($name = null)
    {
        $this->connect();

        if (is_null($name)) {
            return $this->connection->lastInsertId();
        }

        return $this->connection->lastInsertId($name);
    }

    /**
     * Begin a transaction.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        $this->connect();
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction.
     *
     * @return bool
     */
    public function commit()
    {
        $this->connect();
        return $this->connection->commit();
    }

    /**
     * Rollback a transaction.
     *
     * @return bool
     */
    public function rollBack()
    {
        $this->connect();
        return $this->connection->rollBack();
    }

    /**
     * Check if inside a transaction.
     *
     * @return bool
     */
    public function inTransaction()
    {
        $this->connect();
        return $this->connection->inTransaction();
    }

    /**
     * Get the most recent error code associated with the connection.
     *
     * @return string|null
     */
    public function errorCode()
    {
        $this->connect();
        return $this->connection->errorCode();
    }

    /**
     * Get the most recent error info associated with the connection.
     *
     * @return array
     */
    public function errorInfo()
    {
        $this->connect();
        return $this->connection->errorInfo();
    }

    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return int|bool
     */
    public function exec($statement)
    {
        $this->connect();
        return $this->connection->exec($statement);
    }

    /**
     * Set a connection attribute.
     *
     * @param int $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        $this->connect();
        return $this->connection->setAttribute($attribute, $value);
    }

    /**
     * Retrieve a database connection attribute.
     *
     * @param int $attribute
     *
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        $this->connect();
        return $this->connection->getAttribute($attribute);
    }

    /**
     * Check if a connection is still alive.
     *
     * @return bool
     */
    public function ping()
    {
        $this->connect();
        return $this->connection->ping();
    }

    /**
     * Return an array of available PDO drivers.
     *
     * @return array
     */
    public static function getAvailableDrivers()
    {
        return self::getAvailableDrivers();
    }
}
