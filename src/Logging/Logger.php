<?php namespace Minima\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger extends \Monolog\Logger {
  public function __construct() {
    parent::__construct('Minima');
  }

  public static function build($configuration = array()) {
    $defaultConfiguration = array(
			      'log.level' => 'debug',
			      'log.file' => __DIR__ . '/../../minima.log'
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $loggerFormatter = new LineFormatter();
    $loggerHandler = new StreamHandler($configuration['log.file'], $configuration['log.level'], false);
    $loggerHandler->setFormatter($loggerFormatter);
    $logger = new Logger();
    $logger->pushHandler($loggerHandler);
    return $logger;
  }
}
