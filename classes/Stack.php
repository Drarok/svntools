<?php
/**
 * Simple persistent stack, used for branch stack feature.
 *
 * @category Models
 * @package  svnstash
 */

/**
 * Simple persistent stack, used for branch stack feature.
 */
class Stack
{
	/**
	 * Full path to the stack file.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Cache of the stack.
	 *
	 * @var array
	 */
	protected $_stack = array();

	/**
	 * Constructor.
	 *
	 * @param string $path Path to a directory within the working copy.
	 * @param string $name Name of the stack file to save as.
	 */
	public function __construct($path, $name)
	{
		$this->_setPath($path, $name);

		$this->_loadStack();
	}

	/**
	 * Push a value onto the stack.
	 *
	 * @param mixed $value The value to push onto the stack.
	 *
	 * @return void
	 */
	public function push($value)
	{
		array_push($this->_stack, $value);
		$this->_saveStack();
	}

	/**
	 * Pop the last value off the stack, and return it.
	 *
	 * @return mixed
	 */
	public function pop()
	{
		$value = array_pop($this->_stack);
		$this->_saveStack();
		return $value;
	}

	/**
	 * Calculate and store the full path to the stack file.
	 *
	 * @param string $path Path to a directory within the working copy.
	 * @param string $name Name of the stack file to use.
	 *
	 * @return void
	 */
	protected function _setPath($path, $name)
	{
		$stash = new Stash($path);
		$this->_path = $stash->getStashDirPath() . $name;
	}

	/**
	 * Save the stack to disk.
	 *
	 * @return void
	 */
	protected function _saveStack()
	{
		// Ensure all the files exist.
		$this->_setupPath();

		file_put_contents($this->_path, serialize($this->_stack));
	}

	/**
	 * Load the stack from on-disk into memory.
	 *
	 * @return void
	 */
	protected function _loadStack()
	{
		// Set to an empty array to start with.
		$this->_stack = array();

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
		$this->_stack = $array;
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