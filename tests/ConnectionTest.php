<?php

namespace Denosys\Tests;

use PDO;
use stdClass;
use PHPUnit\Framework\TestCase;
use Denosys\Core\Database\Connection;
use Denosys\Core\Database\Exceptions\BadValueException;
use Denosys\Core\Database\Exceptions\DatabaseException;

/**
 * Class ConnectionTest.
 *
 * @covers \Denosys\Core\Database\Connection
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
     * @covers \Denosys\Core\Database\Connection::connect
     * @covers \Denosys\Core\Database\AbstractConnection::exec
     * @covers \Denosys\Core\Database\AbstractConnection::runQuery
     * @covers \Denosys\Core\Database\AbstractConnection::bindValue
     * @covers \Denosys\Core\Database\AbstractConnection::isConnected
     * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testConnect(): void
    {
        $this->connection->connect();

        $this->assertTrue($this->connection->isConnected());
    }

    /**
     * @covers \Denosys\Core\Database\Connection::disconnect
     * @covers \Denosys\Core\Database\AbstractConnection::exec
     * @covers \Denosys\Core\Database\AbstractConnection::runQuery
     * @covers \Denosys\Core\Database\AbstractConnection::bindValue
     * @covers \Denosys\Core\Database\AbstractConnection::isConnected
     * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testDisconnect(): void
    {
        $this->connection->connect();

        $this->assertTrue($this->connection->isConnected());

        $this->connection->disconnect();

        $this->assertFalse($this->connection->isConnected());
    }

    /**
     * @covers \Denosys\Core\Database\Connection::getConnection
     * @covers \Denosys\Core\Database\AbstractConnection::bindValue
     * @covers \Denosys\Core\Database\AbstractConnection::exec
     * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers \Denosys\Core\Database\AbstractConnection::runQuery
     */
    public function testGetConnection(): void
    {
        $this->assertInstanceOf('PDO', $this->connection->getConnection());
    }

    /**
     * @covers \Denosys\Core\Database\AbstractConnection::exec
     * @covers \Denosys\Core\Database\AbstractConnection::runQuery
     * @covers \Denosys\Core\Database\AbstractConnection::bindValue
     * @covers \Denosys\Core\Database\Exceptions\DatabaseException::__construct
     * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testGetConnectionException(): void
    {
        $this->expectException(DatabaseException::class);

        $connection = new Connection('bad:dns');
        $connection->connect();
    }

    /**
     * @covers \Denosys\Core\Database\AbstractConnection::exec
     * @covers \Denosys\Core\Database\AbstractConnection::runQuery
     * @covers \Denosys\Core\Database\AbstractConnection::bindValue
     * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testDebugInfo(): void
    {
        $this->connection->connect();

        $this->assertIsArray($this->connection->__debugInfo());

        $this->assertArrayHasKey('dsn', $this->connection->__debugInfo());

        $this->assertEquals('sqlite::memory:', $this->connection->__debugInfo()['dsn']);
    }

    /**
     * @covers \Denosys\Core\Database\AbstractConnection::exec
     * @covers \Denosys\Core\Database\AbstractConnection::__call
     * @covers \Denosys\Core\Database\AbstractConnection::runQuery
     * @covers \Denosys\Core\Database\AbstractConnection::bindValue
     * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
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
    * @covers \Denosys\Core\Database\AbstractConnection::exec
    * @covers \Denosys\Core\Database\AbstractConnection::commit
    * @covers \Denosys\Core\Database\AbstractConnection::prepare
    * @covers \Denosys\Core\Database\AbstractConnection::rollBack
    * @covers \Denosys\Core\Database\AbstractConnection::runQuery
    * @covers \Denosys\Core\Database\AbstractConnection::fetchAll
    * @covers \Denosys\Core\Database\AbstractConnection::bindValue
    * @covers \Denosys\Core\Database\AbstractConnection::inTransaction
    * @covers \Denosys\Core\Database\AbstractConnection::beginTransaction
    * @covers \Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
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
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::query
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testQuery()
    {
        $query = "SELECT * FROM users";
        $statement = $this->connection->query($query);
        $this->assertInstanceOf('PDOStatement', $statement);
        $this->assertEquals(10, count($statement->fetchAll(\PDO::FETCH_ASSOC)));
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::quote
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
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

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::prepare
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::fetchAffectedRows
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testFetchAffectedRows()
    {
        $this->assertSame(10, $this->connection->fetchAffectedRows("DELETE FROM users"));
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::prepare
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::fetchOne
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testFetchOne()
    {
        $query = "SELECT id, name FROM users WHERE id = 1";
        $actual = $this->connection->fetchOne($query);
        $expect = [
            'id'   => '1',
            'name' => 'John',
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testBindValues()
    {
        $query = "SELECT * FROM users WHERE id = :id";

        // PDO::PARAM_INT
        $statement = $this->connection->prepareQueryWithValues($query, ['id' => 1]);
        $this->assertInstanceOf('PDOStatement', $statement);

        // PDO::PARAM_BOOL
        $statement = $this->connection->prepareQueryWithValues($query, ['id' => true]);
        $this->assertInstanceOf('PDOStatement', $statement);

        // PDO::PARAM_NULL
        $statement = $this->connection->prepareQueryWithValues($query, ['id' => null]);
        $this->assertInstanceOf('PDOStatement', $statement);

        // string (not a special type)
        $statement = $this->connection->prepareQueryWithValues($query, ['id' => 'abc']);
        $this->assertInstanceOf('PDOStatement', $statement);

        // float (also not a special type)
        $statement = $this->connection->prepareQueryWithValues($query, ['id' => 123.456]);
        $this->assertInstanceOf('PDOStatement', $statement);

        // non-bindable
        $this->expectException(
            BadValueException::class,
            "Cannot bind value of type 'object' to placeholder 'id'"
        );
        $statement = $this->connection->prepareQueryWithValues($query, ['id' => new stdClass()]);
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::lastInsertId
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testLastInsertId()
    {
        $this->insert(['name' => 'Adam']);
        $this->assertEquals(11, $this->connection->lastInsertId());

        $this->insert(['name' => 'Eve']);
        $this->assertEquals(12, $this->connection->lastInsertId('name'));
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::errorCode
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::errorInfo
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testErrorCodeAndErrorInfo()
    {
        $this->assertSame('00000', $this->connection->errorCode());
        $this->assertSame(['00000', null, null], $this->connection->errorInfo());
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::setAttribute
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testSetAttribute()
    {
        $this->assertTrue($this->connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL));
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::getAttribute
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testGetAttribute()
    {
        $this->assertEquals('sqlite', $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * @covers Denosys\Core\Database\AbstractConnection::exec
     * @covers Denosys\Core\Database\AbstractConnection::runQuery
     * @covers Denosys\Core\Database\AbstractConnection::bindValue
     * @covers Denosys\Core\Database\AbstractConnection::prepareQueryWithValues
     */
    public function testGetAvailableDrivers()
    {
        $drivers = $this->connection::getAvailableDrivers();
        $this->assertTrue((bool)count($drivers));
    }
}
