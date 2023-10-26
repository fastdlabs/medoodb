<?php

namespace FastD\Database\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\Database\DatabasePool;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = $container->get('config')->get('database', []);

        $container->add('database', new DatabasePool($config));

        unset($config);
    }
}
