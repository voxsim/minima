<?php namespace Minima\Builder;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class LoggerBuilder extends \Monolog\Logger {
  public static function build($configuration = array()) {
    $defaultConfiguration = array(
			      'log.level' => 'debug',
			      'log.file' => __DIR__ . '/../../minima.log'
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $loggerFormatter = new LineFormatter();
    $loggerHandler = new StreamHandler($configuration['log.file'], $configuration['log.level'], false);
    $loggerHandler->setFormatter($loggerFormatter);
    $logger = new LoggerBuilder('Minima');
    $logger->pushHandler($loggerHandler);
    return $logger;
  }
}
