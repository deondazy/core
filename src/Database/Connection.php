<?php

namespace Deondazy\Core\Database;

use PDO;
use PDOException;
use PDOStatement;
use Deondazy\Core\Database\AbstractConnection;
use Deondazy\Core\Database\Exceptions\DatabaseException;

class Connection extends AbstractConnection
{
    /**
     * The database connection credentials.
     *
     * @var array
     */
    protected array $credentials = [];

    /**
     * Set up the database connection credentials.
     *
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array $options
     *
     * @return void
     */
    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = []
    ) {
        // If no error mode is set, set it to exception
        if (!isset($options[PDO::ATTR_ERRMODE])) {
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        // Set the credentials
        $this->credentials = [
            'dsn'      => $dsn,
            'username' => $username,
            'password' => $password,
            'options'  => $options,
        ];
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
     * @return void
     *
     * @throws DatabaseException
     */
    public function connect(): void
    {
        if ($this->connection) {
            return;
        }

        list($dsn, $username, $password, $options) = array_values($this->credentials);

        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $exception) {
            throw new DatabaseException($exception->getMessage(), (int) $exception->getCode());
        }
    }

    /**
     * Disconnect from the database.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * Hide sensitive information from the stack trace.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'dsn'      => $this->credentials['dsn'],
            'username' => '******',
            'password' => '******',
            'options'  => $this->credentials['options'],
        ];
    }
}
