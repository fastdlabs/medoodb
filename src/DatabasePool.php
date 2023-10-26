<?php

namespace FastD\MedooProvider;


class DatabasePool
{
    /**
     * @var Database[]
     */
    protected array $connections = [];

    /**
     * @var array
     */
    protected array $config;

    /**
     * Database constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConnection(string $key, bool $force = false): Database
    {
        if ($force || !isset($this->connections[$key])) {
            if (!isset($this->config[$key])) {
                throw new \LogicException(sprintf('No set %s database', $key));
            }
            $config = $this->config[$key];
            $this->connections[$key] = new Database([
                'type' => $config['adapter'] ?? 'mysql',
                'database' => $config['name'],
                'host' => $config['host'],
                'username' => $config['user'],
                'password' => $config['pass'],
                'charset' => $config['charset'] ?? 'utf8',
                'port' => $config['port'] ?? 3306,
                'prefix' => $config['prefix'] ?? '',
                'option' => $config['option'] ?? [],
                'command' => $config['command'] ?? [],
            ]);
        }

        return $this->connections[$key];
    }

    public function initConnections()
    {
        foreach ($this->config as $name => $config) {
            $this->getConnection($name, true);
        }
    }
}
