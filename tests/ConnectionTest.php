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
            $this->insert(['name' =>  $name]);
        }
    }

    protected function insert(array $data)
    {
        $columns = array_keys($data);
        $values = [];
        foreach ($columns as $col) {
            $values[] = ":$col";
        }
        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $query = "INSERT INTO users ({$columns}) VALUES ({$values})";
        $this->connection->runQuery($query, $data);
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
        $connection->connect();
    }

    /**
     * @covers \Deondazy\Core\Database\AbstractConnection::exec
     * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testDebugInfo(): void
    {
        $this->connection->connect();

        $this->assertIsArray($this->connection->__debugInfo());

        $this->assertArrayHasKey('dsn', $this->connection->__debugInfo());

        $this->assertEquals('sqlite::memory:', $this->connection->__debugInfo()['dsn']);
    }

    /**
     * @covers \Deondazy\Core\Database\AbstractConnection::exec
     * @covers \Deondazy\Core\Database\AbstractConnection::__call
     * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testCall(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM blows up on this test.');
            return;
        }

        $this->connection->sqliteCreateFunction('foo', function () {
        });
        $this->expectException('BadMethodCallException');
        $this->connection->sqliteNoSuchMethod();
    }

   /**
    * @covers \Deondazy\Core\Database\AbstractConnection::exec
    * @covers \Deondazy\Core\Database\AbstractConnection::commit
    * @covers \Deondazy\Core\Database\AbstractConnection::prepare
    * @covers \Deondazy\Core\Database\AbstractConnection::rollBack
    * @covers \Deondazy\Core\Database\AbstractConnection::runQuery
    * @covers \Deondazy\Core\Database\AbstractConnection::fetchAll
    * @covers \Deondazy\Core\Database\AbstractConnection::bindValue
    * @covers \Deondazy\Core\Database\AbstractConnection::inTransaction
    * @covers \Deondazy\Core\Database\AbstractConnection::beginTransaction
    * @covers \Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
    */
    public function testTransactions()
    {
        // data
        $cols = ['name' => 'Joe'];

        // begin and rollback
        $this->assertFalse($this->connection->inTransaction());
        $this->connection->beginTransaction();
        $this->assertTrue($this->connection->inTransaction());
        $this->insert($cols);
        $actual = $this->connection->fetchAll("SELECT * FROM users");
        $this->assertSame(11, count($actual));
        $rollBackResult = $this->connection->rollback();
        $this->assertFalse($this->connection->inTransaction());

        $actual = $this->connection->fetchAll("SELECT * FROM users");
        $this->assertSame(10, count($actual));

        // begin and commit
        $this->assertFalse($this->connection->inTransaction());
        $this->connection->beginTransaction();
        $this->assertTrue($this->connection->inTransaction());
        $this->insert($cols);
        $this->connection->commit();
        $this->assertFalse($this->connection->inTransaction());

        $actual = $this->connection->fetchAll("SELECT * FROM users");
        $this->assertSame(11, count($actual));
        $this->assertTrue($rollBackResult);
    }

    /**
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::query
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testQuery()
    {
        $query = "SELECT * FROM users";
        $statement = $this->connection->query($query);
        $this->assertInstanceOf('PDOStatement', $statement);
        $this->assertEquals(10, count($statement->fetchAll(\PDO::FETCH_ASSOC)));
    }

    /**
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::quote
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
    */
    public function testQuote()
    {
        // quote a string
        $this->assertEquals("'\"abc\" xyz ''opq'''", $this->connection->quote('"abc" xyz \'opq\''));

        // quote an integer
        $this->assertEquals("'123'", $this->connection->quote(123));

        // quote a float
        $this->assertEquals("'123.456'", $this->connection->quote(123.456));

        // quote an array
        $this->assertEquals("'\"foo\"', 'bar', '''baz'''", $this->connection->quote(['"foo"', 'bar', "'baz'"]));

        // quote a null
        $this->assertSame("''", $this->connection->quote(null));
    }
}
