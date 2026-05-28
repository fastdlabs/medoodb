<?php

declare(strict_types=1);

namespace FastD\MedooDB\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\MedooDB\DatabasePool;
use FastD\MedooDB\Listener\BootedEventListener;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // 从容器获取数据库配置
        if ($container->has('config')) {
            $config = $container->get('config');
            $dbConfig = is_array($config) ? ($config['database'] ?? []) : [];
        } else {
            $dbConfig = [];
        }
        
        $dbPool = new DatabasePool($dbConfig);
        $container->add('medoodb', $dbPool);
        
        // 如果事件组件可用，注册监听器
        if ($container->has('event')) {
            $container->get('event')->addListener(new BootedEventListener());
        }
    }
}
