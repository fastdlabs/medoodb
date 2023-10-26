<?php

namespace FastD\MedooDB\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\MedooDB\DatabasePool;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = config()->load(app()->getPath() . '/src/config/database.php');

        $container->add('database', new DatabasePool($config));

        unset($config);
    }
}
