<?php

declare(strict_types=1);

namespace FastD\MedooDB;

use Exception;
use Medoo\Medoo;
use PDO;
use PDOStatement;

class Database extends Medoo
{
    public function __construct(protected array $config)
    {
        parent::__construct($config);

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // 对于SQLite等驱动，某些属性可能不受支持
        try {
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            // 忽略不支持的属性设置
        }
        
        try {
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        } catch (\PDOException $e) {
            // 忽略不支持的属性设置
        }
    }

    public function reconnect(): void
    {
        $this->__construct($this->config);
    }

    public function query(string $statement, array $map = []): ?PDOStatement
    {
        try {
            return parent::query($statement, $map);
        } catch (Exception $e) {
            $this->reconnect();
            return parent::query($statement, $map);
        }
    }

    public function exec(string $statement, array $map = [], ?callable $callback = null): ?PDOStatement
    {
        try {
            return parent::exec($statement, $map, $callback);
        } catch (Exception $e) {
            $this->reconnect();
            return parent::exec($statement, $map, $callback);
        }
    }
}
