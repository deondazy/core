<?php

namespace Deondazy\Tests;

use Deondazy\Core\Database;
use Deondazy\Core\Database\Exceptions\DatabaseException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Deondazy\Core\Database
 */
class DatabaseTest extends TestCase
{
    private $database;

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

    public function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped("Need 'pdo_sqlite' to test in memory.");
        }

        // Set the PDO object
        $this->database = $this->getDatabase();

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
        return new Database('sqlite::memory:');
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

        $this->database->connect();
        $this->database->query($sql);
        $this->database->execute();
    }

    /**
     * Insert some data for testing.
     *
     * @return void
     */
    private function insertData()
    {
        foreach ($this->data as $id => $name) {
            $this->database->query('INSERT INTO users (id, name) VALUES (:id, :name)');
            $this->database->bind(':id', $id);
            $this->database->bind(':name', $name);
            $this->database->execute();
        }
    }

    /**
     * @covers \Deondazy\Core\Database::connect
     */
    public function testConnect()
    {
        $this->database->connect();

        $this->assertTrue($this->database->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database::isConnected
     */
    public function testIsConnected()
    {
        $this->assertTrue($this->database->isConnected());

        $this->database->close();

        $this->assertFalse($this->database->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database::close
     */
    public function testClose()
    {
        $this->database->connect();

        $this->assertTrue($this->database->isConnected());

        $this->database->close();

        $this->assertFalse($this->database->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database::query
     */
    public function testQuery()
    {
        $this->assertInstanceOf('PDOStatement', $this->database->query('SELECT 1'));
    }

    /**
     * @covers \Deondazy\Core\Database::rawQuery
     */
    public function testRawQuery()
    {
        $this->assertEquals(10, count($this->database->rawQuery('SELECT * FROM users')->fetchAll()));
    }

    /**
     * @covers \Deondazy\Core\Database::insert
     */
    public function testInsert()
    {
        $this->database
            ->insert('users', [
            'name' => 'Smith',
        ]);

        $this->assertEquals(11, count($this->database
            ->select('users')
            ->fetchAll()
        ));

        $this->database
            ->insert('users', [
            'name' => 'Manny',
        ], true);

        $this->assertEquals(12, count($this->database
            ->select('users')
            ->fetchAll()
        ));
    }

    /**
     * @covers \Deondazy\Core\Database::update
     */
    public function testUpdate()
    {
        $this->database
            ->update('users')
            ->set(['name' => 'Jude'])
            ->where(['id' => 1])
            ->run();

        $this->assertEquals('Jude', $this->database
            ->select('users')
            ->where(['id' => 1])
            ->fetchOne()
            ->name
        );

        $this->database
            ->update('users')
            ->set(['name' => 'Matin'])
            ->where(['id' => 5])
            ->run();

        $this->assertEquals('Matin', $this->database
            ->select('users')
            ->where(['id' => 5])
            ->fetchOne()
            ->name
        );
    }

    /**
     * @covers \Deondazy\Core\Database::orderBy
     */
    public function testOrderBy()
    {
        $this->assertEquals(1, $this->database
            ->select('users')
            ->orderBy('id', 'ASC')
            ->fetchOne()
            ->id
        );

        $this->assertEquals(10, $this->database
            ->select('users')
            ->orderBy('id', 'DESC')
            ->fetchOne()
            ->id
        );
    }

    /**
     * @covers \Deondazy\Core\Database::offset
     */
    public function testOffset()
    {
        $this->assertEquals(1, $this->database
            ->select('users')
            ->limit(1)
            ->offset(0)
            ->fetchOne()
            ->id
        );

        $this->assertEquals(2, $this->database
            ->select('users')
            ->limit(1)
            ->offset(1)
            ->fetchOne()
            ->id
        );
    }

    /**
     * @covers \Deondazy\Core\Database::delete
     */
    public function testDelete()
    {
        $this->database
            ->delete('users')
            ->run();

        $this->assertEquals(0, count($this->database
            ->select('users')
            ->fetchAll()
        ));
    }

    /**
     * @covers \Deondazy\Core\Database::select
     */
    public function testSelect()
    {
        $this->assertEquals('John', $this->database
            ->select('users')
            ->where(['name' => 'John'])
            ->fetchOne()
            ->name
        );
    }
}
