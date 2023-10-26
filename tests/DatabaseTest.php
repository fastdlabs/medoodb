<?php

namespace tests;

use FastD\MedooProvider\Database;
use FastD\MedooProvider\DatabasePool;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testConnect()
    {
        $config = include __DIR__ . '/database.php';
        $pool = new DatabasePool($config);
        $database = $pool->getDatabase('local');
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(Medoo::class, $database);
    }

    public function testInitConnections()
    {
        $config = include __DIR__ . '/database.php';
        $pool = new DatabasePool($config);
        $pool->initConnections();
        $database = $pool->getDatabase('local');
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(Medoo::class, $database);
    }
}
