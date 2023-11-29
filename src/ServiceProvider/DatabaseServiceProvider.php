<?php

namespace FastD\MedooDB\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\MedooDB\DatabasePool;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \ErrorException
     */
    public function register(Container $container): void
    {
        $config = config()->replace(app()->getBootstrap('database'));
        config()->add(['database' => $config]);
        $container->add('database', new DatabasePool($config));
        unset($config);
    }
}
