<?php namespace Minima\Util;

class Stringify {
  public static function varToString($var)
  {
    if (is_object($var)) {
      return sprintf('Object(%s)', get_class($var));
    }

    if (is_array($var)) {
      $a = array();
      foreach ($var as $k => $v) {
	$a[] = sprintf('%s => %s', $k, static::varToString($v));
      }

      return sprintf('Array(%s)', implode(', ', $a));
    }

    if (is_resource($var)) {
      return sprintf('Resource(%s)', get_resource_type($var));
    }

    if (null === $var) {
      return 'null';
    }

    if (false === $var) {
      return 'false';
    }

    if (true === $var) {
      return 'true';
    }

    return (string) $var;
  }

  public static function parametersToString(array $parameters)
  {
    $pieces = array();
    foreach ($parameters as $key => $val) {
      $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
    }

    return implode(', ', $pieces);
  }
}
