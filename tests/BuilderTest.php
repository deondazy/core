<?php

namespace Deondazy\Tests;

use PHPUnit\Framework\TestCase;
use Deondazy\Core\Database\Connection;
use Deondazy\Core\Database\Query\Builder;

/**
 * Class ConnectionTest.
 *
 * @covers \Deondazy\Core\Database\Query\Builder
 */
class BuilderTest extends TestCase
{
    private Connection $connection;

    private Builder $queryBuilder;

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

        // Set the query builder
        $this->queryBuilder = new Builder($this->connection);

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
            name VARCHAR(5) DEFAULT NULL
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
     * Test the constructor.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     *
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(Builder::class, $this->queryBuilder);

        // Abstract Builder class instance
        $this->assertInstanceOf(\Deondazy\Core\Database\Query\AbstractBuilder::class, $this->queryBuilder);
    }

    /**
     * Test the table method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getTable
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     *
     * @return void
     */
    public function testTable()
    {
        $this->queryBuilder->table('users');

        $this->assertEquals('users', $this->queryBuilder->getTable());
    }

    /**
     * Test the select method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testSelect()
    {
        $this->queryBuilder->table('users')->select('name')->get();
        $this->assertEquals(
            'SELECT name FROM users',
            $this->queryBuilder->getQuery()
        );

        $this->queryBuilder->table('users')->select('name', 'id')->get();
        $this->assertEquals(
            'SELECT name, id FROM users',
            $this->queryBuilder->getQuery()
        );
    }

    /**
     * Test the where method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testWhere()
    {
        $query = $this->queryBuilder->table('users')->select('name')->where('id', 10)->get();
        $this->assertEquals('Sue', $query['name']);
    }

    /**
     * Test the get method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testGet()
    {
        $statement = $this->queryBuilder->table('users')->select('name')->where('id', 1)->get();
        $this->assertEquals(
            'John',
            $statement['name']
        );
    }

    /**
     * Test the get method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testGetAll()
    {
        $query = $this->queryBuilder
            ->table('users')
            ->select('name')
            ->get();

        $this->assertEquals(1, count($query));
    }

    /**
     * Test setWhereClause method Exception.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     *
     * @return void
     */
    public function testSetWhereClauseException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->table('users')->select('name')->where(null)->get();
    }

    /**
     * Test setWhereClause method callable.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testSetWhereClauseCallable()
    {
        $this->queryBuilder->table('users')->select('name')->where(function ($query) {
            $query->where('id', 1);
        })->get();

        $this->assertEquals(
            'SELECT name FROM users WHERE id = :id0',
            $this->queryBuilder->getQuery()
        );
    }

    /**
     * Test orwhere method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testOrWhere()
    {
        $this->queryBuilder->table('users')->select('name')->where('id', 1)->orWhere('id', 2)->get();
        $this->assertEquals(
            'SELECT name FROM users WHERE id = :id0 OR id = :id1',
            $this->queryBuilder->getQuery()
        );
    }

    /**
     * Test rawWhere method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testRawWhere()
    {
        $data = $this->queryBuilder
            ->table('users')
            ->select('name')
            ->rawWhere('WHERE id = :id', ['id' => 1])
            ->get();

        $this->assertEquals(1, count($data));
    }

    /**
     * Test where between method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testWhereBetween()
    {
        $this->queryBuilder
            ->table('users')
            ->select('name')
            ->where('id', 'BETWEEN', [1, 2])
            ->get();

        $expectedQuery = 'SELECT name FROM users WHERE id BETWEEN :id_btw0 AND :id_btw1';

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
    }

    /**
     * Test where not between method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testWhereNotBetween()
    {
        $this->queryBuilder
            ->table('users')
            ->select('name')
            ->where('id', 'NOT BETWEEN', [1, 2])
            ->get();

        $expectedQuery = 'SELECT name FROM users WHERE id NOT BETWEEN :id_btw0 AND :id_btw1';

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
    }

    /**
     * Test where is null method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testWhereNull()
    {
        $this->insert(['name' => null]);

        $this->queryBuilder
            ->table('users')
            ->select('id')
            ->whereNull('name')
            ->get();

        $expectedQuery = 'SELECT id FROM users WHERE name IS NULL';
        $expectedData = 11;

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
        $this->assertEquals($expectedData, $this->queryBuilder->get()['id']);
    }

    /**
     * Test where is not null method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testWhereNotNull()
    {
        $this->queryBuilder
            ->table('users')
            ->select('name')
            ->whereNotNull('name')
            ->get();

        $expectedQuery = 'SELECT name FROM users WHERE name IS NOT NULL';
        $expectedData = 'John';

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
        $this->assertEquals($expectedData, $this->queryBuilder->get()['name']);
    }

    /**
     * Test or where null method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testOrWhereNull()
    {
        $this->queryBuilder
            ->table('users')
            ->select('name')
            ->whereNull('name')
            ->orWhereNull('id')
            ->get();

        $expectedQuery = 'SELECT name FROM users WHERE name IS NULL OR id IS NULL';

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
    }

    /**
     * Test or where not null method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testOrWhereNotNull()
    {
        $this->queryBuilder
            ->table('users')
            ->select('name')
            ->whereNotNull('name')
            ->orWhereNotNull('id')
            ->get();

        $expectedQuery = 'SELECT name FROM users WHERE name IS NOT NULL OR id IS NOT NULL';
        $expectedData = 'John';

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
        $this->assertEquals($expectedData, $this->queryBuilder->get()['name']);
    }

    /**
     * Test where in method.
     *
     * @covers Deondazy\Core\Database\Connection::connect
     * @covers Deondazy\Core\Database\Connection::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::exec
     * @covers Deondazy\Core\Database\AbstractConnection::prepare
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::table
     * @covers Deondazy\Core\Database\AbstractConnection::runQuery
     * @covers Deondazy\Core\Database\AbstractConnection::bindValue
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::getQuery
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::__construct
     * @covers Deondazy\Core\Database\AbstractConnection::prepareQueryWithValues
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeSelect
     * @covers Deondazy\Core\Database\Query\AbstractBuilder::composeWhereClauseConditions
     *
     * @return void
     */
    public function testWhereIn()
    {
        $this->queryBuilder
            ->table('users')
            ->select('name')
            ->whereIn('id', [1, 2, 3])
            ->get();

        $expectedQuery = 'SELECT name FROM users WHERE id IN (:id_in_0, :id_in_1, :id_in_2)';
        $expectedData = 'John';

        $this->assertEquals($expectedQuery, $this->queryBuilder->getQuery());
        $this->assertEquals($expectedData, $this->queryBuilder->get()['name']);
    }
}
