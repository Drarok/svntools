<?php

abstract class Autoloader
{
	public static function autoload($className)
	{
		$classSuffix = str_replace('_', DS, strtolower($className));
		$classPath = SVNSTASH_ROOT . 'classes' . DS . $classSuffix . '.php';
		
		if (file_exists($classPath)) {
			require_once $classPath;
			return class_exists($className, false);
		} else {
			throw new Exception('No such class: ' . $className);
		}
	}
}

spl_autoload_register('Autoloader::autoload');