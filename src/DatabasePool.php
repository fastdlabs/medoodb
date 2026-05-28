<?php

declare(strict_types=1);

namespace FastD\MedooDB;

use RuntimeException;

class DatabasePool
{
    protected array $connections = [];

    public function __construct(protected array $config)
    {
    }

    public function getDatabase(string $key, bool $reconnect = false): Database
    {
        if ($reconnect || !isset($this->connections[$key])) {
            if (!isset($this->config[$key])) {
                throw new RuntimeException(sprintf('No set "%s" database', $key));
            }
            $this->connections[$key] = $this->connect($this->config[$key]);
        }
        return $this->connections[$key];
    }

    protected function connect(array $config): Database
    {
        // 支持 SQLite 等使用 'type' 的配置格式
        if (isset($config['type'])) {
            return new Database($config);
        }
        
        // 原有的 MySQL 配置格式
        return new Database([
            'type'      => $config['adapter'] ?? 'mysql',
            'host'      => $config['host'],
            'database'  => $config['database'],
            'username'  => $config['username'],
            'password'  => $config['password'],
            'charset'   => $config['charset'] ?? 'utf8',
            'port'      => $config['port'] ?? 3306,
            'prefix'    => $config['prefix'] ?? '',
            'option'    => $config['option'] ?? [],
            'command'   => $config['command'] ?? [],
        ]);
    }

    public function initConnections(): void
    {
        foreach ($this->config as $name => $config) {
            $this->connections[$name] = $this->connect($config);
        }
    }
}
