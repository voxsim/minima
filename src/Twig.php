<?php

class Twig extends \Twig_Environment {
  public function __construct($configuration) {
    $filesystem = new \Twig_Loader_Filesystem($configuration['twig.path']);

    $loaderArray = new \Twig_Loader_Array(array());

    $loader = new \Twig_Loader_Chain(array($loaderArray, $filesystem));

    $options = array(
	'charset'          => isset($configuration['charset']) ? $configuration['charset'] : 'UTF-8',
	'debug'            => isset($configuration['debug']) ? $configuration['debug'] : false,
	'strict_variables' => isset($configuration['debug']) ? $configuration['debug'] : false
    );

    parent::__construct($loader, $options);
  }
}
