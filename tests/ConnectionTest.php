<?php

namespace Deondazy\Tests;

use PHPUnit\Framework\TestCase;
use Deondazy\Core\Database\Connection;
use Deondazy\Core\Database\AbstractConnection;
use Deondazy\Core\Database\Exceptions\DatabaseException;

/**
 * Class ConnectionTest.
 *
 * @covers \Deondazy\Core\Database\Connection
 */
class ConnectionTest extends TestCase
{
    private Connection $connection;

    private $data = [
        1  => 'John',
        2  => 'Mike',
        3  => 'Fred',
        4  => 'Jane',
        5  => 'Mary',
        6  => 'Scott',
        7  => 'Sara',
        8  => 'Sally',
        9  => 'Sam',
        10 => 'Sue',
    ];

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped("Need 'pdo_sqlite' to test in memory.");
        }

        // Set the PDO object
        $this->connection = $this->getDatabase();

        // Create a table
        $this->createTable();

        // Insert some data
        $this->insertData();
    }

    private function getDatabase()
    {
        /**
         * Using SQLite in-memory database for testing
         * so we don't pollute the real database with test data.
         *
         * @see https://www.sqlite.org/inmemorydb.html
         */
        return new Connection('sqlite::memory:');
    }

    /**
     * Create a table for testing.
     *
     * @return void
     */
    private function createTable()
    {
        $sql = 'CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(5) NOT NULL
        )';

        $this->connection->connect();
        $this->connection->exec($sql);
    }

    /**
     * Insert some data for testing.
     *
     * @return void
     */
    private function insertData()
    {
        foreach ($this->data as $id => $name) {
            $this->connection->runQuery('INSERT INTO users (id, name) VALUES (:id, :name)', [
                'id'   => $id,
                'name' => $name,
            ]);
        }
    }

    /**
     * @covers \Deondazy\Core\Database\Connection::connect
     * @covers \Deondazy\Core\Database\AbstractConnection::exec
     * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers \Deondazy\Core\Database\AbstractConnection::isConnected
     * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testConnect(): void
    {
        $this->connection->connect();

        $this->assertTrue($this->connection->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database\Connection::disconnect
     * @covers \Deondazy\Core\Database\AbstractConnection::exec
     * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers \Deondazy\Core\Database\AbstractConnection::isConnected
     * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testDisconnect(): void
    {
        $this->connection->connect();

        $this->assertTrue($this->connection->isConnected());

        $this->connection->disconnect();

        $this->assertFalse($this->connection->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database\Connection::getConnection
     * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers \Deondazy\Core\Database\AbstractConnection::exec
     * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
     */
    public function testGetConnection(): void
    {
        $this->assertInstanceOf('PDO', $this->connection->getConnection());
    }

    /**
     * @covers \Deondazy\Core\Database\AbstractConnection::exec
     * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers \Deondazy\Core\Database\Exceptions\DatabaseException::__construct
     * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testGetConnectionException(): void
    {
        $this->expectException(DatabaseException::class);

        $connection = new Connection('bad:dns');
        $connection->getConnection();
    }
}
