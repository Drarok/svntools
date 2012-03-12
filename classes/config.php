<?php
/**
 * Simple configuration loading.
 *
 * @category Helpers
 * @package  svnstash
 */

/**
 * Config helper.
 *
 * This class implements simple config loading and access.
 */
abstract class Config
{
	/**
	 * In-memory cache, keyed on the filename.
	 * 
	 * @var array
	 */
	static protected $_configs = array();

	/**
	 * Getter for config data.
	 * 
	 * Pass keys in using the format filename.key1.key2 to traverse arrays.
	 * 
	 * @param string $key     Keypath to the config value.
	 * @param mixed  $default Default value to return if the keypath isn't found.
	 * 
	 * @return mixed
	 */
	static public function get($key, $default = null)
	{
		$keyParts = explode('.', $key, 2);

		$file = null;
		$keyPath = null;

		if (count($keyParts) >= 1) {
			$file = $keyParts[0];
		}

		if (count($keyParts) == 2) {
			$keyPath = $keyParts[1];
		}

		if (! $file) {
			throw new Exception('Invalid config key: ' . $key);
		}

		$config = static::_load($file);

		if (! $keyPath) {
			// If only the filename was passed in, return the whole config array.
			return $config;
		}

		return static::_traverse($config, $keyPath, $default);
	}

	/**
	 * Load a config file from disk, or the in-memory cache.
	 * 
	 * @param string $file File name without extension.
	 * 
	 * @return array
	 */
	static protected function _load($file)
	{
		if (array_key_exists($file, static::$_configs)) {
			return static::$_configs[$file];
		}

		$path = SVNTOOLS_ROOT . 'config' . DS . $file . '.php';
		if (file_exists($path)) {
			$config = include $path;
		} else {
			$config = array();
		}

		// Cache and return the data.
		return static::$_configs[$file] = $config;
	}

	/**
	 * Array traversing helper method.
	 * 
	 * @param array  $arr     Array to operate on.
	 * @param string $key     Keypath to use.
	 * @param mixed  $default Default value to return if the keypath isn't found.
	 * 
	 * @return mixed
	 */
	static protected function _traverse($arr, $key, $default = null)
	{
		if (! is_array($arr)) {
			throw new Exception('Invalid input: ' . gettype($arr));
		}

		// Break up the keys into an array.
		$keys = explode('.', $key);

		$obj = $arr;
		while (true) {
			// Grab the next key.
			$currentKey = array_shift($keys);

			if (array_key_exists($currentKey, $obj)) {
				$obj = $obj[$currentKey];

				if (count($keys) == 0) {
					// There are no more keys left, we found it!
					return $obj;
				}
			} else {
				// Key doesn't exist, so return the default.
				return $default;
			}
		}
	}
}