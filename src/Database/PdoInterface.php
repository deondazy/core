<?php

namespace Deondazy\Core\Database;

use PDO;
use PDOStatement;

interface PdoInterface
{
    /**
     * Begin a transaction and turn off autocommit.
     *
     * @return bool
     */
    public function beginTransaction();

    /**
     * Commit a transaction and turn on autocommit.
     *
     * @return bool
     */
    public function commit();

    /**
     * Rollback a transaction and turn on autocommit.
     *
     * @return bool
     */
    public function rollBack();

    /**
     * Check if inside a transaction.
     *
     * @return bool
     */
    public function inTransaction();

    /**
     * Get the most recent error code associated with the connection.
     *
     * @return string|null
     */
    public function errorCode();

    /**
     * Get the most recent error info associated with the connection.
     *
     * @return array|null
     */
    public function errorInfo();

    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return int|false
     */
    public function exec(string $statement);

    /**
     * Set a connection attribute.
     *
     * @param int $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function setAttribute(int $attribute, mixed $value);

    /**
     * Retrieve a database connection attribute.
     *
     * @param int $attribute
     *
     * @return mixed
     */
    public function getAttribute(int $attribute);

    /**
     * Return an array of available PDO drivers.
     *
     * @return array
     */
    public static function getAvailableDrivers();

    /**
     * Prepare an SQL statement.
     *
     * @param string $statement
     * @param array $driverOptions
     *
     * @return PDOStatement|false
     */
    public function prepare(string $statement, array $driverOptions = []);

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * @param string $statement
     * @param int|null $fetchMode
     * @param mixed|null $fetchArgument
     *
     * @return PDOStatement|false
     */
    public function query(string $statement, int $fetchMode = null, mixed ...$fetchArgument);

    /**
     * Quotes a string for use in a query.
     *
     * @param mixed $value
     * @param int $parameterType
     *
     * @return string|false
     */
    public function quote(mixed $value, int $parameterType = PDO::PARAM_STR);

    /**
     * Return the ID of the last inserted row or sequence value.
     *
     * @param string|null $name
     *
     * @return string|false
     */
    public function lastInsertId(string $name = null);
}
