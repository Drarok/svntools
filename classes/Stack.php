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
class Upstream
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

	public function push($value)
	{
	}

	public function pop()
	{
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

	protected function _loadStack()
	{
	}

	protected function _saveStack()
	{
	}
}