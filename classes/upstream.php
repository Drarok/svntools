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
	 * Constructor.
	 *
	 * @param string $path Path to a directory within the working copy.
	 */
	public function __construct($path)
	{
		$this->_setPath($path);
	}

	/**
	 * Add an 'upstream' entry for the given path.
	 *
	 * @param string $alias     Path to add the upstream entry for.
	 * @param string $upstream Upstream path to store.
	 *
	 * @return void
	 */
	public function addUpstream($alias, $upstream)
	{
		$upstreams = $this->getAllUpstreams();
		$upstreams[$alias] = $upstream;
		$this->_saveUpstreams($upstreams);
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
		$upstreams = $this->getAllUpstreams();
		return array_key_exists($alias, $upstreams)
			? $upstreams[$alias]
			: null;
	}

	/**
	 * Get all upstream configurations.
	 *
	 * @return array
	 */
	public function getAllUpstreams()
	{
		if (! file_exists($this->_path)) {
			return array();
		}

		$contents = file_get_contents($this->_path);

		if (! $contents) {
			return array();
		}

		$array = unserialize($contents);

		if (! is_array($array)) {
			return array();
		}

		return $array;
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
		$upstreams = $this->getAllUpstreams();
		unset($upstreams[$alias]);
		$this->_saveUpstreams($upstreams);
	}

	/**
	 * Store the passed-in config to disk.
	 *
	 * @param array $upstreams Current config to save.
	 *
	 * @return void
	 */
	protected function _saveUpstreams($upstreams)
	{
		// Ensure all the files exist.
		$this->_setupPath();

		file_put_contents($this->_path, serialize($upstreams));
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