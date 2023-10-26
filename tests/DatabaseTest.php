<?php

namespace tests;

use FastD\Config\Config;
use FastD\Container\Container;
use FastD\Database\Database;
use FastD\Database\DatabasePool;
use FastD\Database\ServiceProvider\DatabaseServiceProvider;
use FastD\Runtime\Runtime;
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

    public function testServiceProvider()
    {
        $container = new Container();
        $config = new Config();
        $config->merge([
            'database' => load(__DIR__ . '/database.php')
        ]);
        $container->add('config', $config);
        Runtime::$container = $container;
        $serviceProvider = new DatabaseServiceProvider();
        $container->register($serviceProvider);
        $database = $container->get('database')->getDatabase('local');
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(Medoo::class, $database);
    }
}
