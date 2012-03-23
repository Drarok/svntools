<?php
/**
 * Class autoloading.
 *
 * @category Helpers
 * @package  svnstash
 */

/**
 * Autoloader helper.
 *
 * This class just wraps the autoload method.
 */
abstract class Autoloader
{
	/**
	 * Attempt to load a class based on its name.
	 *
	 * @param string $className Name of the class to load.
	 *
	 * @return bool If the class is found, returns true.
	 *
	 * @throws Exception When failing to load a class.
	 */
	public static function autoload($className)
	{
		$classSuffix = str_replace('_', DS, strtolower($className));
		$classPath = SVNTOOLS_ROOT . 'classes' . DS . $classSuffix . '.php';
		
		if (file_exists($classPath)) {
			include_once $classPath;
			return class_exists($className, false);
		} else {
			throw new Exception('No such class: ' . $className);
		}
	}
}

// Register this autoloader.
spl_autoload_register('Autoloader::autoload');