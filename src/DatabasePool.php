<?php

namespace FastD\MedooDB;


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

    public function getDatabase(string $key, bool $reconnect = false): Database
    {
        if ($reconnect || !isset($this->connections[$key])) {
            if (!isset($this->config[$key])) {
                throw new \LogicException(sprintf('No set "%s" database', $key));
            }
            $this->connections[$key] = $this->connect($this->config[$key]);
        }
        return $this->connections[$key];
    }

    protected function connect(array $config): Database
    {
        return new Database([
            'type' => $config['adapter'] ?? 'mysql',
            'host' => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => $config['charset'] ?? 'utf8',
            'port' => $config['port'] ?? 3306,
            'prefix' => $config['prefix'] ?? '',
            'option' => $config['option'] ?? [],
            'command' => $config['command'] ?? [],
        ]);
    }

    public function initConnections()
    {
        foreach ($this->config as $name => $config) {
            $this->connections[$name] = $this->connect($config);
        }
    }
}
