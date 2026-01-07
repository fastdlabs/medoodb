<?php

declare(strict_types=1);

namespace FastD\MedooDB\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\MedooDB\DatabasePool;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $dbPool = new DatabasePool($container->need('database'));
        $container->add('medoodb', $dbPool);
        $container->add('onWorkerStart', [$dbPool]);
    }
}
