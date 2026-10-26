<?php

declare(strict_types=1);

use FastD\Container\Container;
use FastD\MedooDB\Database;
use FastD\MedooDB\ServiceProvider\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;

final class ProviderConfigTest extends TestCase
{
    public function testReadsDatabaseConfigFromFastDContainer(): void
    {
        $container = new class extends Container {
            public function __construct()
            {
                $this->add('config', new stdClass());
            }

            public function config(string $key): array
            {
                return [
                    'local' => [
                        'type' => 'sqlite',
                        'database' => ':memory:',
                        'charset' => 'utf8',
                        'prefix' => '',
                    ],
                ];
            }
        };

        (new DatabaseServiceProvider())->register($container);

        $this->assertInstanceOf(Database::class, $container->get('medoodb')->getDatabase('local'));
    }

    public function testMissingConfigServiceThrows(): void
    {
        $this->expectException(RuntimeException::class);

        (new DatabaseServiceProvider())->register(new Container());
    }

}
