<?php
/**
 * Upstream management.
 *
 * @category Models
 * @package  svnstash
 */

/**
 * Upstream management class.
 *
 * This class is a sort-of model, which aims to abstract the management of
 * upstreams, similar to the Stash class.
 */
class Upstream
{
	const UPSTREAMSFILE = 'upstreams.txt';

	/**
	 * Full path to the upstreams file.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Cache of the configured upstreams.
	 *
	 * @var array
	 */
	protected $_upstreams = array();

	/**
	 * Constructor.
	 *
	 * @param string $path Path to a directory within the working copy.
	 */
	public function __construct($path)
	{
		$this->_setPath($path);
		$this->_loadUpstreams();
	}

	/**
	 * Add an 'upstream' entry for the given path.
	 *
	 * @param string $alias    Path to add the upstream entry for.
	 * @param string $upstream Upstream path to store.
	 *
	 * @return void
	 */
	public function addUpstream($alias, $upstream)
	{
		$this->_upstreams[$alias] = $upstream;
		$this->_saveUpstreams();
	}

	/**
	 * Get the upstream (if any) for the given alias.
	 *
	 * @param string $alias Path to fetch the upstream setting for.
	 *
	 * @return mixed
	 */
	public function getUpstream($alias)
	{
		return array_key_exists($alias, $this->_upstreams)
			? $this->_upstreams[$alias]
			: null;
	}

	/**
	 * Get all upstream configurations.
	 *
	 * @return array
	 */
	public function getAllUpstreams()
	{
		return $this->_upstreams;
	}

	/**
	 * Remove the upstream setting for the given alias.
	 *
	 * @param string $alias Path to remove the upstream config for.
	 *
	 * @return void
	 */
	public function removeUpstream($alias)
	{
		unset($this->_upstreams[$alias]);
		$this->_saveUpstreams();
	}

	/**
	 * Remove all the currently-configured upstreams.
	 *
	 * @return void
	 */
	public function removeAllUpstreams()
	{
		$this->_upstreams = array();
		$this->_saveUpstreams();
	}

	/**
	 * Store the current upstreams config to disk.
	 *
	 * @return void
	 */
	protected function _saveUpstreams()
	{
		// Ensure all the files exist.
		$this->_setupPath();

		file_put_contents($this->_path, serialize($this->_upstreams));
	}

	/**
	 * Load the on-disk upstreams into the cache.
	 *
	 * @return void
	 */
	protected function _loadUpstreams()
	{
		// Set to an empty array to start with.
		$this->_upstreams = array();

		if (! file_exists($this->_path)) {
			// No file? Bail.
			return;
		}

		$contents = file_get_contents($this->_path);

		if (! $contents) {
			// Nothing in it? Bail.
			return;
		}

		$array = unserialize($contents);

		if (! is_array($array)) {
			// Invalid contents? Bail.
			return;
		}

		// Looking good, let's cache that.
		$this->_upstreams = $array;
	}

	/**
	 * Calculate and store the full path to the upstreams file.
	 *
	 * @param string $path Path to a directory within the working copy.
	 *
	 * @return void
	 */
	protected function _setPath($path)
	{
		$stash = new Stash($path);
		$this->_path = $stash->getStashDirPath() . static::UPSTREAMSFILE;
	}

	/**
	 * Ensure the directory and file exist.
	 *
	 * @return void
	 */
	protected function _setupPath()
	{
		$dir = dirname($this->_path);

		if (! is_dir($dir)) {
			if (! mkdir($dir, 0755, true)) {
				throw new Exception('Failed to create path: ' . $dir);
			}
		}

		if (! file_exists($this->_path)) {
			if (! touch($this->_path)) {
				throw new Exception('Failed to create file: ' . $this->_path);
			}
		}
	}
}