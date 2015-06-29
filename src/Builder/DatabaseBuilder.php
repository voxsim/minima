<?php

namespace Minima\Builder;

class DatabaseBuilder
{
    public static function getConnection($configuration = array())
    {
        $defaultConfiguration = array(
                  'database.name' => 'minima',
                  'database.user' => 'root',
                  'database.password' => '',
                  'database.host' => 'localhost',
                  'database.host' => 'pdo_mysql',
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        $config = new \Doctrine\DBAL\Configuration();
        $params = array(
            'dbname' => $configuration['database.name'],
            'user' => $configuration['database.user'],
            'password' => $configuration['database.password'],
            'host' => $configuration['database.host'],
            'driver' => $configuration['database.driver'],
        );

        return \Doctrine\DBAL\DriverManager::getConnection($params, $config);
    }
}
