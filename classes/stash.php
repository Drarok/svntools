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
	 * Directory within STASHDIR to store trash files.
	 *
	 * @const string
	 */
	const TRASHDIR = 'trash';
	
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
	 * Get the full path to the stash directory.
	 *
	 * @return string
	 */
	public function getStashDirPath()
	{
		return $this->_path . DS . self::STASHDIR . DS;
	}

	/**
	 * Get the name of every stashed change, in the order they were stashed.
	 *
	 * @return array
	 */
	public function getStashes()
	{
		// Make sure everything is set up.
		$this->_setupPath();

		$result = array();
		
		$file = fopen($this->_getStashFilePath(), 'r');
		
		if (! $file) {
			throw new Exception('Failed to open '
				. $this->_getStashFilePath());
		}
		
		while ($stash = trim(fgets($file))) {
			$result[] = $stash;
		}
		
		fclose($file);
		
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
		return $this->getStashDirPath() . $name . '.diff';
	}
	
	/**
	 * Create and store a new stash.
	 *
	 * @param string $name              Name of the stash to create.
	 * @param bool   $includeUntracked  Include 'untracked' files in the stash.
	 * @param bool   $revertWorkingCopy Should we immediately run a 'revert' on the working copy?
	 *
	 * @return void
	 */
	public function addStash($name, $includeUntracked, $revertWorkingCopy = NULL)
	{
		if (! is_bool($revertWorkingCopy)) {
			throw new Exception('You *must* specify $revertWorkingCopy as a boolean.');
		}

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
		
		$diffFile = $this->getStashDirPath() . $name . '.diff';
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
		
		if ($revertWorkingCopy) {
			// Revert the working copy.
			$svn->revert('.', true);
		}
		
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
		
		// Trash the stash.
		$this->_trashStash($path);
		
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
		$path = $this->getStashDirPath();
		
		$dir = opendir($path);
		
		$files = array();
		while ($entry = readdir($dir)) {
			if ($entry[0] == '.') {
				// Ignore dotfiles.
				continue;
			}
			
			$ext = substr($entry, strrpos($entry, '.'));
			if ($ext != '.diff') {
				// Only include diff files.
				continue;
			}
			
			
			// This is slightly inefficient, but it allows us to keep the code
			// for trashing a stash in one place.
			$this->removeStash(basename($entry, '.diff'));
		}
	}
	
	/**
	 * Get the file listing from the trash.
	 *
	 * Note that this method only returns the *filenames*.
	 *
	 * @return array
	 */
	public function getTrash()
	{
		$result = array();
		
		foreach (glob($this->_getTrashDirPath() . '*.diff') as $entry) {
			$result[] = basename($entry, '.diff');
		}
		
		// Make sure they're in order.'
		sort($result);
		
		return $result;
	}
	
	/**
	 * Get the file path for a trash item bu id or name.
	 *
	 * @param mixed $id Numeric index from getTrash, or the stash name.
	 *
	 * @return string
	 */
	protected function _getTrashItemPath($id)
	{
		if (is_numeric($id)) {
			$id = (int) $id;
			$trash = $this->getTrash();
			if (! array_key_exists($id, $trash)) {
				throw new Exception('No such item in the trash: ' . $id);
			}
			
			$name = $trash[$id];
		} else {
			$name = $id;
		}
		
		// Build the full path and return.
		$fullPath = $this->_getTrashDirPath() . $name . '.diff';
		
		if (! file_exists($fullPath)) {
			throw new Exception('No such stash in trash: ' . $name);
		}
		
		return $fullPath;
	}
	
	/**
	 * Restore an item from the trash, using its numeric id, or name.
	 *
	 * @param mixed $id Numeric index from getTrash(), or the filename.
	 *
	 * @return void
	 */
	public function restoreFromTrash($id)
	{
		$fullTrashedPath = $this->_getTrashItemPath($id);
		
		// Work out the original filename.
		$originalFilename = basename($fullTrashedPath);
		$originalFilename = substr($originalFilename, strpos($originalFilename, '-') + 1);
		
		// Make sure there's not already a stash with that name.
		if (file_exists($stashFullPath = $this->getStashDirPath() . $originalFilename)) {
			throw new Exception('A stash named ' . $originalFilename . ' already exists!');
		}
		
		if (! rename($fullTrashedPath, $stashFullPath)) {
			throw new Exception('Failed to restore ' . $filename);
		}
		
		// Append the new stash name to the stash file.
		$file = fopen($this->_getStashFilePath(), 'a');
		if (! $file) {
			throw new Exception('Failed to open ' . $this->_getStashFilePath());
		}
		fwrite($file, basename($originalFilename, '.diff'). PHP_EOL);
		fclose($file);
		
		return basename($originalFilename, '.diff');
	}
	
	/**
	 * Permanently erase a stash from the trash.
	 *
	 * @param mixed $id Numeric index from getTrash, or the trashed filename.
	 *
	 * @return void
	 */
	public function eraseFromTrash($id)
	{
		$fullTrashedPath = $this->_getTrashItemPath($id);
		$name = basename($fullTrashedPath, '.diff');
		$name = substr($name, strpos($name, '-') + 1);
		
		if (! unlink($fullTrashedPath)) {
			throw new Exception('Failed to erase trashed stash: ' . $name);
		}
		
		return $name;
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
		$this->_path = Svn::getRoot($path);
		chdir($this->_path);
	}

	/**
	 * Get the full path to the stash file.
	 *
	 * @return string
	 */
	protected function _getStashFilePath()
	{
		return $this->getStashDirPath() . self::STASHFILE;
	}
	
	/**
	 * Get the fll path to the trash directory.
	 *
	 * @return string
	 */
	protected function _getTrashDirPath()
	{
		return $this->getStashDirPath() . static::TRASHDIR . DS;
	}
	
	
	/**
	 * Set up the stash directory ready for use.
	 *
	 * @return void
	 */
	protected function _setupPath()
	{
		$stashDir = $this->getStashDirPath();
		
		if (! is_dir($stashDir) && ! mkdir($stashDir, 0755, true)) {
			throw new Exception('Cannot create stash directory: ' . $stashDir);
		}
		
		$stashFile = $this->_getStashFilePath();
		
		if (! file_exists($stashFile) && ! touch($stashFile)) {
			throw new Exception('Cannot create stash file: ' . $stashFile);
		}
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
	
	/**
	 * Move a stashed change into the trash folder inside our stash directory.
	 *
	 * @param string $path Path to the stash to move.
	 *
	 * @return void
	 */
	protected function _trashStash($path)
	{
		
		$trashDir = $this->_getTrashDirPath();
		
		if (! is_dir($trashDir)) {
			if (! mkdir($trashDir, 0755, true)) {
				throw new Exception('Cannot create trash directory: ' . $trashDir);
			}
		}
		
		// Create a new date-based filename.
		$trashFile = date('YmdHis') . '-' . basename($path);
		
		// Don't overwrite any existing files.
		if (file_exists($trashDir . $trashFile)) {
			throw new Exception('A trash file already exists! Please try again.');
		}
		
		// Alright, do the move.
		if (! rename($path, $trashDir . $trashFile)) {
			throw new Exception('Failed to trash file.');
		}
	}
}