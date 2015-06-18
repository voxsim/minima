<?php namespace Minima\Builder;

class DatabaseBuilder {
  public static function getConnection() {
    $config = new \Doctrine\DBAL\Configuration();
    $params = array(
	'dbname' => 'evoxcondomini',
	'user' => 'root',
	'password' => 'root',
	'host' => 'localhost',
	'driver' => 'pdo_mysql',
    );
    return \Doctrine\DBAL\DriverManager::getConnection($params, $config);
  }
}
