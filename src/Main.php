<?php

namespace ROrier\Core;

use Exception;
use ROrier\Config\Tools\CollectionTool;
use ROrier\Core\Components\Bootstrapper;
use ROrier\Core\Interfaces\AppInterface;
use ROrier\Core\Interfaces\MainInterface;

abstract class Main implements MainInterface
{
    protected const CUSTOM_CONFIGURATION = [];

    static private ?AppInterface $app = null;

    /**
     * @inheritDoc
     * @throws Exception
     */
    static public function boot(array $runtimeConfiguration = []): Bootstrapper
    {
        $config = static::CUSTOM_CONFIGURATION;

        static::inferConfiguration(
            $config,
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)
        );

        CollectionTool::merge($config, $runtimeConfiguration);

        return static::buildBootstrapper($config);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    static protected function buildBootstrapper(array $config): Bootstrapper
    {
        return new Bootstrapper($config);
    }

    /**
     * @param array $config
     * @param array $stack
     */
    static protected function inferConfiguration(array &$config, array $stack): void
    {
        $trace = array_pop($stack);

        if (is_array($trace) && array_key_exists('file', $trace) && !empty($trace['file'])) {
            $config['root'] = dirname($trace['file']);
        }

        $config['main_class_name'] = static::class;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    static public function app(): AppInterface
    {
        if (static::$app === null) {
            throw new Exception("App is undefined. Use boot() method to build application.");
        }

        return self::$app;
    }

    /**
     * @inheritDoc
     */
    static public function save(AppInterface $app): void
    {
        static::$app = $app;
    }

    /**
     * @inheritDoc
     */
    static public function ready(): bool
    {
        return (static::$app !== null);
    }
}
