# FastD MedooDB

[![Build Status](https://travis-ci.org/fastdlabs/medoodb.svg?branch=master)](https://travis-ci.org/fastdlabs/medoodb)
[![Latest Stable Version](https://poser.pugx.org/fastd/medoodb/v/stable)](https://packagist.org/packages/fastd/medoodb) 
[![Total Downloads](https://poser.pugx.org/fastd/medoodb/downloads)](https://packagist.org/packages/fastd/medoodb) 
[![License](https://poser.pugx.org/fastd/medoodb/license)](https://packagist.org/packages/fastd/medoodb)

FastD MedooDB 是基于 Medoo 的数据库组件，提供简洁的数据库操作接口，支持多连接池管理和 FastD 框架集成。

## ✨ 特性

- 🎯 **基于 Medoo**: 继承 Medoo 的所有功能，轻量且高效
- 🔗 **连接池管理**: 支持多数据库连接池，自动管理连接
- 🔌 **多数据库支持**: MySQL、PostgreSQL、SQLite、MariaDB、MSSQL 等
- 🚀 **FastD 集成**: 完美集成 FastD 框架，支持服务提供者
- ⚡ **自动重连**: 连接断开时自动重连
- 🎨 **简洁 API**: 链式调用，简单易用

## 📋 环境要求

- **PHP**: >= 8.2
- **扩展**: 
  - ext-pdo
  - ext-pdo_mysql (MySQL)
  - ext-pdo_sqlite (SQLite)
  - 或其他需要的 PDO 驱动
- **依赖**: 
  - catfan/medoo ^2.2

## 📦 安装

```bash
composer require fastd/medoodb
```

## 🚀 快速开始

### 1. 基本使用

```php
<?php

use FastD\MedooDB\Database;

// 创建数据库连接
$database = new Database([
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'test',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'port' => 3306,
    'prefix' => '',
]);

// 查询数据
$users = $database->select('users', '*');

// 条件查询
$users = $database->select('users', '*', [
    'status' => 1,
    'ORDER' => 'created_at DESC',
    'LIMIT' => 10
]);

// 插入数据
$database->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// 更新数据
$database->update('users', [
    'status' => 1
], [
    'id' => 1
]);

// 删除数据
$database->delete('users', [
    'status' => 0
]);
```

### 2. SQLite 内存数据库（测试用）

```php
<?php

use FastD\MedooDB\Database;

// 使用 SQLite 内存数据库
$database = new Database([
    'type' => 'sqlite',
    'database' => ':memory:',
    'charset' => 'utf8',
]);

// 创建表
$database->query("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL
)");

// 插入数据
$database->insert('users', [
    'name' => 'Test User',
    'email' => 'test@example.com'
]);

// 查询
$users = $database->select('users', '*');
print_r($users);
```

### 3. 数据库连接池

```php
<?php

use FastD\MedooDB\DatabasePool;

// 配置多个数据库连接
$config = [
    'local' => [
        'type' => 'mysql',
        'host' => 'localhost',
        'database' => 'app_db',
        'username' => 'root',
        'password' => 'password',
        'charset' => 'utf8mb4',
    ],
    'analytics' => [
        'type' => 'mysql',
        'host' => 'analytics.example.com',
        'database' => 'analytics_db',
        'username' => 'reader',
        'password' => 'reader_password',
        'charset' => 'utf8mb4',
    ],
    'cache' => [
        'type' => 'sqlite',
        'database' => '/path/to/cache.db',
    ],
];

// 创建连接池
$pool = new DatabasePool($config);

// 获取数据库连接
$localDb = $pool->getDatabase('local');
$users = $localDb->select('users', '*');

// 获取分析数据库连接
$analyticsDb = $pool->getDatabase('analytics');
$stats = $analyticsDb->select('stats', '*');

// 重新连接
$localDb = $pool->getDatabase('local', true);

// 初始化所有连接
$pool->initConnections();
```

### 4. FastD 框架集成

```php
<?php

use FastD\Container\Container;
use FastD\MedooDB\ServiceProvider\DatabaseServiceProvider;

// 创建容器
$container = new Container();

// 配置数据库
$container->add('config', [
    'database' => [
        'local' => [
            'type' => 'mysql',
            'host' => 'localhost',
            'database' => 'app_db',
            'username' => 'root',
            'password' => 'password',
        ],
    ],
]);

// 注册服务提供者
$serviceProvider = new DatabaseServiceProvider();
$serviceProvider->register($container);

// 使用数据库
$dbPool = $container->get('medoodb');
$database = $dbPool->getDatabase('local');

$users = $database->select('users', '*');
```

### 5. 查询构建器示例

```php
<?php

use FastD\MedooDB\Database;

$database = new Database([
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'test',
    'username' => 'root',
    'password' => 'password',
]);

// WHERE 条件
$users = $database->select('users', '*', [
    'AND' => [
        'status' => 1,
        'OR' => [
            'role' => 'admin',
            'role' => 'moderator'
        ]
    ]
]);

// JOIN 查询
$posts = $database->select('posts', [
    '[>]users' => ['user_id' => 'id']
], [
    'posts.id',
    'posts.title',
    'users.name',
    'users.email'
]);

// 聚合查询
$count = $database->count('users', '*', [
    'status' => 1
]);

$sum = $database->sum('orders', 'amount', [
    'status' => 'completed'
]);

// 原生 SQL
$result = $database->query("SELECT * FROM users WHERE status = :status", [
    ':status' => 1
]);

// 事务处理
$database->action(function($database) {
    $database->insert('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
    
    $userId = $database->id();
    
    $database->insert('profiles', [
        'user_id' => $userId,
        'bio' => 'Hello World'
    ]);
});
```

### 6. 高级配置

```php
<?php

use FastD\MedooDB\Database;
use PDO;

$database = new Database([
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'test',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'port' => 3306,
    'prefix' => 'app_',
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
    'command' => [
        'SET SQL_MODE=ANSI_QUOTES'
    ]
]);
```

## 📚 API 参考

### 核心类

- `FastD\MedooDB\Database` - 数据库操作类（继承 Medoo）
- `FastD\MedooDB\DatabasePool` - 数据库连接池管理
- `FastD\MedooDB\ServiceProvider\DatabaseServiceProvider` - FastD 服务提供者
- `FastD\MedooDB\Listener\BootedEventListener` - 启动事件监听器

### 主要方法

#### Database 类

继承 Medoo 的所有方法：

- `select($table, $columns, $where)` - 查询数据
- `insert($table, $data)` - 插入数据
- `update($table, $data, $where)` - 更新数据
- `delete($table, $where)` - 删除数据
- `count($table, $where)` - 计数
- `query($sql, $map)` - 执行原生 SQL
- `action($callback)` - 事务处理
- `reconnect()` - 重新连接
- `pdo` - 获取 PDO 实例

#### DatabasePool 类

- `getDatabase($key, $reconnect)` - 获取数据库连接
- `initConnections()` - 初始化所有连接
- `onCallback()` - 回调事件（用于 Swoole 启动）

## 🧪 测试

```bash
composer install
vendor/bin/phpunit
```

测试使用 SQLite 内存数据库，无需配置真实数据库。

## 🤝 贡献

欢迎贡献代码、报告问题或提出建议！

- 🐛 [报告问题](https://github.com/fastdlabs/medoodb/issues)
- 💡 提交功能建议
- 🔧 贡献代码和文档
- ⭐ Star 项目支持

## 📄 License

MIT License

## 🔗 相关链接

- [Medoo 官方文档](https://medoo.in/doc)
- [FastD 框架](https://github.com/fastdlabs/fastd)
- [Packagist](https://packagist.org/packages/fastd/medoodb)
