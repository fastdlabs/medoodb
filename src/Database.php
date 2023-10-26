<?php

namespace FastD\MedooProvider;

use Medoo\Medoo;
use PDO;
use PDOStatement;

class Database extends Medoo
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var PDO
     */
    public $pdo;

    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct($this->config);

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }

    /**
     * reconnect database.
     */
    public function reconnect()
    {
        $this->__construct($this->config);
    }

    public function query(string $statement, array $map = []): ?PDOStatement
    {
        try {
            return parent::query($statement, $map);
        } catch (\Exception $e) {
            $this->reconnect();
            return parent::query($statement, $map);
        }
    }

    public function exec(string $statement, array $map = [], callable $callback = null): ?PDOStatement
    {
        try {
            return parent::exec($statement, $map, $callback);
        } catch (\Exception $e) {
            $this->reconnect();
            return parent::exec($statement, $map, $callback);
        }
    }
}
