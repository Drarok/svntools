<?php
/**
 * Simple on-disk caching.
 *
 * @category Cache
 * @package  svnstash
 */

/**
 * This class implements a simple on-disk cache for key-value pairs.
 */
class Cache
{
	/**
	 * Helper method to determine if a cache with the given name exists.
	 * 
	 * @param string $name Name of the cache to look for.
	 * 
	 * @return bool
	 */
	static public function exists($name)
	{
		return file_exists(SVNTOOLS_ROOT . 'cache' . DS . $name);
	}

	/**
	 * Helper method to delete an on-disk cache, if it exists.
	 * 
	 * @param string $name Name of the cache to delete.
	 * 
	 * @return void
	 */
	static public function delete($name)
	{
		if (Cache::exists($name)) {
			unlink(SVNTOOLS_ROOT . 'cache' . DS . $name);
		}
	}

	/**
	 * Name of this cache (its filename).
	 * 
	 * @var string
	 */
	protected $_name;

	/**
	 * Absolute path to the cache file.
	 * 
	 * @var string
	 */
	protected $_path;

	/**
	 * Timestamp when this data was created.
	 * 
	 * @var int
	 */
	protected $_created;

	/**
	 * Timestamp when the cache data expires.
	 * 
	 * @var int
	 */
	protected $_expires;

	/**
	 * In-memory storage of the cached data.
	 * 
	 * @var array
	 */
	protected $_data;

	/**
	 * Constructor.
	 * 
	 * @param string $filename Name of the cache.
	 * @param mixed  $lifetime Seconds to allow the data to live, or null for infinite.
	 */
	public function __construct($name, $lifetime)
	{
		$this->_name = $name;
		$this->_path = SVNTOOLS_ROOT . 'cache' . DS . $name;

		$this->_created = time();

		if ($lifetime === null) {
			$this->_expires = null;
		} else {
			$this->_expires = $this->_created + $lifetime;
		}

		// Initialise the data storage.
		$this->_data = array();

		// Attempt to load any existing data, which will override
		// $_created and $_lifetime, if required.
		$this->_load();
	}

	/**
	 * Getter method to access the key-value pairs.
	 * 
	 * @param mixed $key     Key to fetch the value for.
	 * @param mixed $default Value to return if it's not in the storage.
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

	/**
	 * Public method, exposing the internal _save method.
	 * 
	 * @return void
	 */
	public function save() {
		$this->_save();
	}

	/**
	 * Internal method to load the data from the cache.
	 * 
	 * This method checks the lifetime, and loads nothing if the data has expired.
	 * 
	 * @return void
	 */
	protected function _load()
	{
		if (! file_exists($this->_path)) {
			// There's no file to attempt to load.
			return;
		}

		$wrapper = unserialize(file_get_contents($this->_path));

		if ($wrapper->expires > $this->_created) {
			// The data hasn't expired, so load it up.
			$this->_created = $wrapper->created;
			$this->_expires = $wrapper->expired;
			$this->_data = $wrapper->data;
		}
	}

	/**
	 * Internal method to save the data to disk.
	 * 
	 * Note: This method will *not* write to disk if the data has expired.
	 * 
	 * @return void
	 */
	protected function _save()
	{
		if ($this->_expires <= time()) {
			// The data has expired, so don't save, and delete the file if there is one.
			Cache::delete($this->_name);
			return;
		} else {
			$wrapper = (object) array(
				'created' => $this->_created,
				'expires' => $this->_expires,
				'data' => $this->_data,
			);

			file_put_contents($this->_path, serialize($wrapper));
		}
	}
}