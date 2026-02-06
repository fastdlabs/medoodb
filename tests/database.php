<?php

// 测试用数据库配置（使用 SQLite 内存数据库以便于测试）
return [
    'local' => [
        'type' => 'sqlite',
        'database' => ':memory:',
        'charset' => 'utf8',
        'prefix' => '',
        'option' => [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'command' => [],
    ],
    'secondary' => [
        'type' => 'sqlite',
        'database' => ':memory:',
        'charset' => 'utf8',
        'prefix' => '',
        'option' => [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'command' => [],
    ],
];