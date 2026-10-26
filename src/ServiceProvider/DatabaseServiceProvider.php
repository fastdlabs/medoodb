<?php

declare(strict_types=1);

namespace FastD\MedooDB\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\MedooDB\DatabasePool;
use FastD\MedooDB\Listener\BootedEventListener;
use Throwable;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $dbPool = new DatabasePool($this->config($container));
        $container->add('medoodb', $dbPool);

        if ($container->has('event')) {
            $container->get('event')->addListener(new BootedEventListener());
        }
    }

    private function config(Container $container): array
    {
        // Application 形态：直接向容器请求已经注册的配置文件
        if (method_exists($container, 'config')) {
            try {
                $config = $container->config('database');
                if (is_array($config)) {
                    return $config;
                }
            } catch (Throwable) {
                // 回退到 config 服务解析
            }
        }

        if (!$container->has('config')) {
            return [];
        }

        $config = $container->get('config');

        // 测试/独立使用：直接注入数组
        if (is_array($config)) {
            $nested = $config['database'] ?? null;
            return is_array($nested) ? $nested : $config;
        }

        // FastD 应用：config 服务是 FileParser
        if (is_object($config) && method_exists($config, 'parse')) {
            $root = method_exists($container, 'getRootPath') ? $container->getRootPath() : getcwd();
            $file = $root . '/config/database.php';

            if (is_file($file)) {
                $parsed = $config->parse($file);
                $value = is_object($parsed) && method_exists($parsed, 'get')
                    ? $parsed->get('database', [])
                    : [];

                return is_array($value) ? $value : [];
            }
        }

        return [];
    }
}
