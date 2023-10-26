<?php

namespace FastD\MedooProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;

class MedooProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = $container->get('config')->get('database', []);

        $container->add('database', new DatabasePool($config));

        unset($config);
    }
}
