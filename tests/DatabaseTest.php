<?php

namespace Deondazy\Tests;

use Deondazy\Core\Database;
use Deondazy\Core\Exception\DatabaseException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Deondazy\Core\Database
 */
class DatabaseTest extends TestCase
{
    protected $pdo;

    public function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped("Need 'pdo_sqlite' to test in memory.");
        }

        /**
         * Using SQLite in-memory database for testing
         * so we don't pollute the real database with test data.
         * 
         * @see https://www.sqlite.org/inmemorydb.html
         */
        $this->pdo = Database::instance()->connect('sqlite::memory:');

        // Create a table
        $this->createTable();

        // Insert some data
        $this->insertData();
    }

    /**
     * Create a table for testing.
     * 
     * @return void
     */
    private function createTable()
    {
        Database::instance()->query(
            "CREATE TABLE IF NOT EXISTS users 
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        );

        Database::instance()->execute();
    }

    /**
     * Insert some data for testing.
     * 
     * @return void
     */
    private function insertData()
    {
        Database::instance()->query(
            "INSERT INTO users (name, email, password) VALUES 
            ('John Doe', 'johndoe@email.com', 'password'),
            ('Jane Doe', 'janedoe@email.com', 'password')"
        );

        Database::instance()->execute();
    }

    /**
     * @covers \Deondazy\Core\Database::instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Database::class, Database::instance());
    }

    /**
     * @covers \Deondazy\Core\Database::connect
     */
    public function testConnect()
    {
        $this->assertInstanceOf('PDO', Database::instance()->connect($this->pdo));
    }

    /**
     * @covers \Deondazy\Core\Database::isConnected
     */
    public function testIsConnected()
    {
        $this->assertTrue(Database::instance()->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database::close
     */
    public function testClose()
    {
        Database::instance()->close();

        $this->assertFalse(Database::instance()->isConnected());
    }

    /**
     * @covers \Deondazy\Core\Database::query
     */
    public function testQuery()
    {
        $this->assertInstanceOf('PDOStatement', Database::instance()->query('SELECT 1'));
    }

    /**
     * @covers \Deondazy\Core\Database::select
     */
    public function testSelect()
    {
        $this->assertEquals('John Doe', Database::instance()
            ->select('users')
            ->where(['name' => 'John Doe'])
            ->fetchOne()
            ->name
        );
    }

    /**
     * @covers \Deondazy\Core\Database::insert
     */
    public function testInsert()
    {
        $this->assertNotEquals(0, Database::instance()->insert('users', [
            'name' => 'Sam Smith',
            'email' => 'samsmith@email.com',
            'password' => 'password'
        ]));
    }

    /**
     * @covers \Deondazy\Core\Database::update
     */
    public function testUpdate()
    {
        $this->assertEquals(1, Database::instance()
            ->update('users')
            ->set(['email' => 'mikesmith@email.com'])
            ->where(['id' => 1])
            ->run()
        );
    }

    /**
     * @covers \Deondazy\Core\Database::delete
     */
    public function testDelete()
    {
        $this->assertEquals(1, Database::instance()
            ->delete('users')
            ->where(['email' => 'mikesmith@email.com'])
            ->run()
        );
    }
}