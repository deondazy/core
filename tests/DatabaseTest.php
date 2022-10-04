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

    
}