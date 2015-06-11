<?php namespace Minima;

// SMELL
class Twig extends \Twig_Environment {
  public function __construct($configuration) {
    $defaultConfiguration = array(
			      'debug' => false,
			      'charset' => 'UTF-8',
			      'twig.path' => __DIR__.'/../views'
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $filesystem = new \Twig_Loader_Filesystem($configuration['twig.path']);
    $loaderArray = new \Twig_Loader_Array(array());
    $loader = new \Twig_Loader_Chain(array($loaderArray, $filesystem));

    $options = array(
	'charset'          => $configuration['charset'],
	'debug'            => $configuration['debug'],
	'strict_variables' => $configuration['debug']
    );

    parent::__construct($loader, $options);
  }

  public static function create($configuration = array()) {
    return new Twig($configuration);
  }
}
