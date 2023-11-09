<?php


use FastD\Application;
use FastD\MedooDB\Database;
use FastD\MedooDB\DatabasePool;
use FastD\MedooDB\ServiceProvider\DatabaseServiceProvider;
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
        $application = new Application([
            'env' => 'tests',
            'path' => __DIR__,
            'app' => [],
            'routes' => [],
            'services' => [],
            'database' => __DIR__ . '/database.php',
        ]);
        $server = new \FastD\Server\FastCGI($application);
        $serviceProvider = new DatabaseServiceProvider();
        $application->register($serviceProvider);
        $database = $application->get('database')->getDatabase('local');
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(Medoo::class, $database);
    }
}
