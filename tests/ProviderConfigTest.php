<?php

declare(strict_types=1);

use FastD\Container\Container;
use FastD\MedooDB\Database;
use FastD\MedooDB\ServiceProvider\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;

final class ProviderConfigTest extends TestCase
{
    private const DB_CONFIG = [
        'local' => [
            'type' => 'sqlite',
            'database' => ':memory:',
            'charset' => 'utf8',
            'prefix' => '',
        ],
    ];

    public function testReadsConfigFromFileParserService(): void
    {
        $root = sys_get_temp_dir() . '/fastd-medoodb-config-' . bin2hex(random_bytes(4));
        mkdir($root . '/config', 0777, true);
        file_put_contents($root . '/config/database.php', '<?php return [];');

        $parser = new class {
            public function parse(string $file): object
            {
                return new class {
                    public function get(string $key, array $default = []): array
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
            }
        };

        $container = new class extends Container {
            public string $root = '';

            public function getRootPath(): string
            {
                return $this->root;
            }
        };
        $container->root = $root;
        $container->add('config', $parser);

        (new DatabaseServiceProvider())->register($container);

        $database = $container->get('medoodb')->getDatabase('local');
        $this->assertInstanceOf(Database::class, $database);

        unlink($root . '/config/database.php');
        rmdir($root . '/config');
        rmdir($root);
    }

    public function testReadsConfigFromApplicationStyleContainer(): void
    {
        $container = new class extends Container {
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
}
