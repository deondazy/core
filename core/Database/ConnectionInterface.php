<?php

namespace Deondazy\Core\Database;

use Deondazy\Core\Database\PdoInterface;
use PDO;
use PDOStatement;

interface ConnectionInterface extends PdoInterface
{
    /**
     * Connect to the database.
     *
     * @return void
     */
    public function connect();

    /**
     * Disconnect from the database.
     *
     * @return void
     */
    public function disconnect();

    /**
     * Get the database connection.
     *
     * @return PDO
     */
    public function getConnection();

    /**
     * Is the database connected?
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Run a query after preparing it with the bound values and return a PDOStatement.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return PDOStatement
     */
    public function runQuery(string $statement, array $values = []);

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
    public function prepareQueryWithValues(string $statement, array $values = []);

    /**
     * Run a query and return the number of affected rows.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return int
     */
    public function fetchAffectedRows(string $statement, array $values = []);

    /**
     * Fetch one row from the result set as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array|null
     */
    public function fetchOne(string $statement, array $values = []);

    /**
     * Fetch all rows from the result set as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAll(string $statement, array $values = []);
}
