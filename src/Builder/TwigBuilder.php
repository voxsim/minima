<?php

namespace Minima\Builder;

class TwigBuilder
{
    public static function build($configuration = array())
    {
        $defaultConfiguration = array(
                  'debug' => false,
                  'charset' => 'UTF-8',
                  'twig.path' => __DIR__.'/../views',
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        $filesystem = new \Twig_Loader_Filesystem($configuration['twig.path']);
        $loaderArray = new \Twig_Loader_Array(array());
        $loader = new \Twig_Loader_Chain(array($loaderArray, $filesystem));

        $options = array(
            'charset' => $configuration['charset'],
            'debug' => $configuration['debug'],
            'strict_variables' => $configuration['debug'],
        );

        return new \Twig_Environment($loader, $options);
    }
}
