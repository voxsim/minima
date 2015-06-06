<?php namespace Minima\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger extends \Minima\Logging\LogListener {
  public function __construct($configuration) {
    $loggerFormatter = new LineFormatter();
    $loggerHandler = new StreamHandler($configuration['log.file'], $configuration['log.level'], false);
    $loggerHandler->setFormatter($loggerFormatter);

    $logger = new \Monolog\Logger('minima');
    $logger->pushHandler($loggerHandler);

    parent::__construct($logger);
  }
}
