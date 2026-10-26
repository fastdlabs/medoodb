<?php

declare(strict_types=1);

namespace FastD\MedooDB\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use FastD\MedooDB\DatabasePool;
use FastD\MedooDB\Listener\BootedEventListener;
use RuntimeException;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        if (!$container->has('config')) {
            throw new RuntimeException('The FastD config service is not registered.');
        }

        $config = $container->config('database');

        if (!is_array($config) || $config === []) {
            throw new RuntimeException('The database configuration is missing.');
        }

        $container->add('medoodb', new DatabasePool($config));

        if ($container->has('event')) {
            $container->get('event')->addListener(new BootedEventListener());
        }
    }
}
