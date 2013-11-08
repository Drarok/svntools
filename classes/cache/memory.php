<?php
/**
 * Simple in-memory caching.
 *
 * @category Cache
 * @package  svnstash
 */

/**
 * This class implements a simple in-memory cache for key-value pairs.
 */
class Cache_Memory
{
	/**
	 * In-memory storage of the cached data.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Getter method to access the key-value pairs.
	 *
	 * @param mixed $key     Key to fetch the value for.
	 * @param mixed $default Value to return if it's not cached.
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return array_key_exists($key, $this->_data)
			? $this->_data[$key]
			: $default;
	}

	/**
	 * Setter for the key-value pairs.
	 *
	 * @param mixed $key   Key to store the value under.
	 * @param mixed $value Value to store.
	 *
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;
	}
}