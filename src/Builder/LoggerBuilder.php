<?php

namespace Minima\Builder;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class LoggerBuilder
{
    public static function build($configuration)
    {
        $defaultConfiguration = array(
                  'log.level' => 'debug',
                  'log.file' => $configuration['root'].'/minima.log',
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        $loggerFormatter = new LineFormatter();
        $loggerHandler = new StreamHandler($configuration['log.file'], $configuration['log.level'], false);
        $loggerHandler->setFormatter($loggerFormatter);
        $logger = new \Monolog\Logger('Minima');
        $logger->pushHandler($loggerHandler);

        return $logger;
    }
}
