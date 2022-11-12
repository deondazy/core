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
     * Test the select method.
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
}
