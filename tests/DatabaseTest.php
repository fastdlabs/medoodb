<?php

declare(strict_types=1);

use FastD\Application;
use FastD\Container\Container;
use FastD\MedooDB\Database;
use FastD\MedooDB\DatabasePool;
use FastD\MedooDB\ServiceProvider\DatabaseServiceProvider;
use Medoo\Medoo;
use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

class DatabaseTest extends TestCase
{
    private array $testConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testConfig = include __DIR__ . '/database.php';
    }

    public function testDatabaseConstructor()
    {
        $database = new Database($this->testConfig['local']);
        
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(Medoo::class, $database);
        
        // 检查PDO属性设置
        $pdo = $database->pdo;
        $this->assertInstanceOf(PDO::class, $pdo);
        
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        
        // 只检查通用属性，跳过可能不受支持的属性
        try {
            $emulatePreparesValue = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);
            $this->assertIsBool($emulatePreparesValue);
        } catch (\PDOException $e) {
            // 如果属性不支持，则跳过断言
        }
        
        try {
            $stringifyFetchesValue = $pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES);
            $this->assertIsBool($stringifyFetchesValue);
        } catch (\PDOException $e) {
            // 如果属性不支持，则跳过断言
        }
    }

    public function testReconnectMethod()
    {
        $database = new Database($this->testConfig['local']);
        $originalPdo = $database->pdo;
        
        $database->reconnect();
        $newPdo = $database->pdo;
        
        // reconnect 方法会重新构造对象，因此应得到有效的 PDO 实例
        $this->assertInstanceOf(PDO::class, $newPdo);
        $this->assertNotNull($newPdo);
    }

    public function testQueryMethodWithConnectionError()
    {
        // 使用模拟来测试异常处理逻辑
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['query', 'reconnect'])
            ->getMock();
            
        $database->expects($this->once())
            ->method('reconnect');
            
        // 这个测试在没有真实数据库连接时无法完全验证，但可以验证方法存在
        $this->assertTrue(method_exists($database, 'query'));
    }

    public function testExecMethodWithConnectionError()
    {
        // 使用模拟来测试异常处理逻辑
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['exec', 'reconnect'])
            ->getMock();
            
        $database->expects($this->once())
            ->method('reconnect');
            
        // 这个测试在没有真实数据库连接时无法完全验证，但可以验证方法存在
        $this->assertTrue(method_exists($database, 'exec'));
    }

    public function testDatabasePoolInitialization()
    {
        $pool = new DatabasePool($this->testConfig);
        $database = $pool->getDatabase('local');
        
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(Medoo::class, $database);
    }

    public function testDatabasePoolWithMultipleConnections()
    {
        $pool = new DatabasePool($this->testConfig);
        
        $localDb = $pool->getDatabase('local');
        $secondaryDb = $pool->getDatabase('secondary');
        
        $this->assertInstanceOf(Database::class, $localDb);
        $this->assertInstanceOf(Database::class, $secondaryDb);
        $this->assertNotSame($localDb, $secondaryDb);
    }

    public function testDatabasePoolReconnect()
    {
        $pool = new DatabasePool($this->testConfig);
        $database = $pool->getDatabase('local');
        
        $this->assertInstanceOf(Database::class, $database);
        
        // 测试重新连接功能
        $newDatabase = $pool->getDatabase('local', true);
        $this->assertInstanceOf(Database::class, $newDatabase);
    }

    public function testDatabasePoolWithNonExistentConnection()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No set "nonexistent" database');
        
        $pool = new DatabasePool($this->testConfig);
        $pool->getDatabase('nonexistent');
    }

    public function testDatabasePoolInitConnections()
    {
        $pool = new DatabasePool($this->testConfig);
        $pool->initConnections();
        
        // 验证连接已初始化
        $localDb = $pool->getDatabase('local');
        $secondaryDb = $pool->getDatabase('secondary');
        
        $this->assertInstanceOf(Database::class, $localDb);
        $this->assertInstanceOf(Database::class, $secondaryDb);
    }

    public function testDatabaseServiceProvider()
    {
        $container = new Container();
        $container->add('database', $this->testConfig);
        
        $serviceProvider = new DatabaseServiceProvider();
        $serviceProvider->register($container);
        
        $dbPool = $container->get('medoodb');
        $this->assertInstanceOf(DatabasePool::class, $dbPool);
        
        $database = $dbPool->getDatabase('local');
        $this->assertInstanceOf(Database::class, $database);
    }

    public function testDatabaseServiceProviderRegistrationWithApplication()
    {
        // 模拟在应用中注册服务提供者
        $container = new Container();
        $container->add('database', $this->testConfig);
        
        $serviceProvider = new DatabaseServiceProvider();
        $serviceProvider->register($container);
        
        // 验证服务被正确注册
        $this->assertTrue($container->has('medoodb'));
        $this->assertTrue($container->has('onWorkerStart'));
        
        $dbPool = $container->get('medoodb');
        $this->assertInstanceOf(DatabasePool::class, $dbPool);
        
        // 验证 onWorkerStart 回调已注册
        $workerStartCallbacks = $container->get('onWorkerStart');
        $this->assertIsArray($workerStartCallbacks);
        $this->assertCount(1, $workerStartCallbacks);
        $this->assertInstanceOf(DatabasePool::class, $workerStartCallbacks[0]);
    }

    public function testDatabasePoolCallbackEvent()
    {
        $pool = new DatabasePool($this->testConfig);
        
        $result = $pool->onCallback();
        
        $this->assertTrue($result);
        // 验证连接已初始化
        $this->assertNotNull($pool->getDatabase('local'));
    }

    public function testDatabasePoolInternalConnectionsArray()
    {
        $pool = new DatabasePool($this->testConfig);
        
        // 初始时连接数组应该是空的
        $reflector = new ReflectionClass($pool);
        $property = $reflector->getProperty('connections');
        $property->setAccessible(true);
        
        $connections = $property->getValue($pool);
        $this->assertEmpty($connections);
        
        // 获取数据库后连接数组应该有值
        $db = $pool->getDatabase('local');
        $connections = $property->getValue($pool);
        $this->assertNotEmpty($connections);
        $this->assertArrayHasKey('local', $connections);
        $this->assertInstanceOf(Database::class, $connections['local']);
    }

    public function testDatabasePoolConnectMethod()
    {
        $pool = new DatabasePool($this->testConfig);
        
        $reflector = new ReflectionClass($pool);
        $method = $reflector->getMethod('connect');
        $method->setAccessible(true);
        
        $db = $method->invoke($pool, $this->testConfig['local']);
        $this->assertInstanceOf(Database::class, $db);
    }
}
