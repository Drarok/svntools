<?php
/**
 * Stash management.
 *
 * @category Models
 * @package  svnstash
 */

/**
 * Stash management class.
 *
 * This class is a sort-of model, which aims to abstract the management of
 * stashes.
 */
class Stash
{
	/**
	 * Directory to store stashes in at the root of the svn working copy.
	 *
	 * @const string
	 */
	const STASHDIR = '.svnstash';
	
	/**
	 * File to store the stashes list in, inside the STASHDIR.
	 *
	 * @const string
	 */
	const STASHFILE = 'svnstash.txt';
	
	/**
	 * Validate a stash name.
	 *
	 * @param string $name Name to validate.
	 *
	 * @return void
	 *
	 * @throws Exception When $name is invalid.
	 */
	protected static function _validateStashName($name)
	{
		if (is_numeric($name)) {
			throw new Exception('Stash names cannot be numeric.');
		}
		
		if (! preg_match('/^[a-z0-9_-]+$/i', $name)) {
			throw new Exception('Invalid stash name. '
				. 'Only a-z, 0-9, underscore and dash are allowed.');
		}
	}
	
	
	
	/**
	 * Path to the root of the working copy.
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
	 * Getter for the $_path ivar.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}
	
	/**
	 * Get the name of every stashed change, in the order they were stashed.
	 *
	 * @return array
	 */
	public function getStashes()
	{
		$result = array();
		
		if ($this->_getIsSetup()) {
			$file = fopen($this->_getStashFilePath(), 'r');
			
			if (! $file) {
				throw new Exception('Failed to open '
					. $this->_getStashFilePath());
			}
			
			while ($stash = trim(fgets($file))) {
				$result[] = $stash;
			}
			
			fclose($file);
		}
		
		return $result;
	}
	
	/**
	 * Get a single stash's path using an identifier.
	 *
	 * @param mixed $id A numeric index, a stash name, or null for the latest.
	 *
	 * @return string
	 */
	public function getStashPath($id = null)
	{
		$stashes = $this->getStashes();
		
		if ($id === null) {
			$index = count($stashes) - 1;
		} elseif (is_numeric($id)) {
			$index = (int) $id;
		} else {
			$index = array_search($id, $stashes);
		}
		
		if ($index === -1
			|| $index === false
			|| ! array_key_exists($index, $stashes)
		) {
			throw new Exception('Failed to find stash with identifier: ' . $id);
		}
		
		$name = $stashes[$index];
		return $this->_getStashDirPath() . $name . '.diff';
	}
	
	/**
	 * Create and store a new stash.
	 *
	 * @param string $name             Name of the stash to create.
	 * @param bool   $includeUntracked Include 'untracked' files in the stash.
	 *
	 * @return void
	 */
	public function addStash($name, $includeUntracked)
	{
		// Make sure the name is valid.
		self::_validateStashName($name);
		
		// Ensure the path is ready.
		$this->_setupPath();
		
		// Make sure the name is unique.
		$currentStashes = $this->getStashes();
		
		if (in_array($name, $currentStashes)) {
			throw new Exception('Stash \'' . $name . '\' already exists.');
		}
		
		// Create a new subversion wrapper instance.
		$svn = new Svn($this->_path);
		
		if (! $includeUntracked) {
			$untrackedFiles = array();
		} else {
			$untrackedFiles = $this->_getUntrackedFiles();
			
			foreach ($untrackedFiles as $untracked) {
				$svn->add($untracked);
			}
		}
		
		// Get the diff from subversion, and store it in .svnstash.
		$diff = $svn->diff();
		
		// Check there's some diff data.
		if (! trim($diff)) {
			throw new Exception('No diff available from subversion.');
		}
		
		$diffFile = $this->_getStashDirPath() . $name . '.diff';
		if (! file_put_contents($diffFile, $diff . PHP_EOL)) {
			throw new Exception('Failed to store diff data to ' . $diffFile);
		}
		
		// Append the new stash name to the stash file.
		$file = fopen($this->_getStashFilePath(), 'a');
		if (! $file) {
			throw new Exception('Failed to open ' . $this->_getStashFilePath());
		}
		fwrite($file, $name . PHP_EOL);
		fclose($file);
		
		// Revert the working copy.
		$svn->revert('.', true);
		
		// Remove the untracked files.
		foreach ($untrackedFiles as $untracked) {
			if (! unlink($untracked)) {
				throw new Exception('Failed to remove file: ' . $untracked);
			}
		}
	}
	
	/**
	 * Remove a stash from the .svnstash directory and list file.
	 *
	 * @param mixed $id A numeric index, a stash name, or null for the latest.
	 *
	 * @return string Name of the removed stash.
	 */
	public function removeStash($id = null)
	{
		$path = $this->getStashPath($id);
		$name = basename($path, '.diff');
		
		if (! unlink($path)) {
			throw new Exception('Failed to delete stash file: ' . $path);
		}
		
		// Now remove the stash from the list file.
		$file = fopen($this->_getStashFilePath(), 'w');
		
		if (! $file) {
			throw new Exception('Failed to open stash file.');
		}
		
		$stashes = $this->getStashes();
		foreach ($stashes as $stash) {
			if ($stash == $name) {
				// Don't output the stash we're removing.
				continue;
			}
			
			fwrite($file, $stash . PHP_EOL);
		}
		
		fclose($file);
		
		return $name;
	}
	
	/**
	 * Remove all stashed changes.
	 *
	 * @return void
	 *
	 * @throws Exception Failing to delete a file throws an Exception.
	 */
	public function removeAllStashes()
	{
		$path = $this->_getStashDirPath();
		
		$dir = opendir($path);
		
		$files = array();
		while ($entry = readdir($dir)) {
			if ($entry[0] == '.') {
				continue;
			}
			
			$files[] = $path . $entry;
		}
		
		foreach ($files as $file) {
			if (! unlink($file)) {
				throw new Exception('Failed to delete ' . $file);
			}
		}
	}
	
	
	
	/**
	 * Set the path where we will save and load stashes.
	 *
	 * This method will traverse up from the passed path, attempting to find 
	 * the root of the working copy. Note that this isn't 100% reliable, but
	 * there doesn't appear to be a reliable way prior to subversion v1.7.
	 *
	 * @param string $path Path to a directory within the working copy.
	 *
	 * @return void
	 */
	protected function _setPath($path)
	{
		// Attempt to find the root of the working copy.
		// This isn't 100% reliable, though.
		$parent = '';
		$grandparent = $path;
		
		while (is_dir($grandparent . DS . '.svn')) {
			$parent = $grandparent;
			$grandparent = dirname($parent);
		}
		
		if (! is_dir($parent . DS . '.svn')) {
			throw new Exception('Failed to find a subversion working copy.');
		}
		
		$this->_path = $parent;
		
		chdir($this->_path);
	}
	
	/**
	 * Get the full path to the stash directory.
	 *
	 * @return string
	 */
	protected function _getStashDirPath()
	{
		return $this->_path . DS . self::STASHDIR . DS;
	}
	
	/**
	 * Get the full path to the stash file.
	 *
	 * @return string
	 */
	protected function _getStashFilePath()
	{
		return $this->_getStashDirPath() . self::STASHFILE;
	}
	
	/**
	 * Set up the stash directory ready for use.
	 *
	 * @return void
	 */
	protected function _setupPath()
	{
		$stashDir = $this->_getStashDirPath();
		
		if (! is_dir($stashDir) && ! mkdir($stashDir, 0755, true)) {
			throw new Exception('Cannot create stash directory: ' . $stashDir);
		}
		
		$stashFile = $this->_getStashFilePath();
		
		if (! file_exists($stashFile) && ! touch($stashFile)) {
			throw new Exception('Cannot create stash file: ' . $stashFile);
		}
	}
	
	/**
	 * Get whether or not the path is set up.
	 *
	 * @return bool
	 */
	protected function _getIsSetup()
	{
		return (
			is_dir($this->_getStashDirPath())
			&& file_exists($this->_getStashFilePath()));
	}
	
	/**
	 * Fetch all untracked files from the working directory.
	 *
	 * @return array
	 */
	protected function _getUntrackedFiles()
	{
		// Create a new Subversion client.
		$svn = new Svn($this->_path);
		
		$result = array();
		
		foreach ($svn->status() as $item) {
			if ($item->getPath() == '.svnstash') {
				// Don't include our own stash directory.
				continue;
			}
			
			if ($item->getState() == Svn_Entry::UNVERSIONED) {
				$result[] = $item->getPath();
			}
		}
		
		return $result;
	}
}