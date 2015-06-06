<?php namespace Minima;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger extends \Monolog\Logger {
  public function __construct($configuration) {
    $loggerFormatter = new LineFormatter();
    $loggerHandler = new StreamHandler($configuration['log.file'], $configuration['log.level'], false);
    $loggerHandler->setFormatter($loggerFormatter);

    parent::__construct('minima');
    $this->pushHandler($loggerHandler);
  }
}
