<?php
/**
 * Subversion client wrapper.
 *
 * @category Models
 * @package  svnstash
 */

/**
 * Subversion client wrapper class.
 */
class Svn
{
	/**
	 * Attempt to traverse up the filesystem, looking for the working copy root.
	 * 
	 * @param string $path Path to start looking at.
	 * 
	 * @return string
	 * 
	 * @throws Exception Failing to find a .svn directory with throw.
	 */
	static public function getRoot($path)
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
		
		return $parent;
	}
	
	/**
	 * Stores the path to the working copy we're working on.
	 *
	 * @var string
	 */
	protected $_path;
	
	/**
	 * Constructor.
	 *
	 * @param string $path Path to the working copy.
	 */
	public function __construct($path)
	{
		$this->_path = $path;
	}
	
	/**
	 * Add a file to the working copy.
	 *
	 * @param string $path Path to add to the working copy.
	 *
	 * @return void
	 */
	public function add($path)
	{
		echo 'Adding: ', $path, PHP_EOL;
		$this->_runCommand('add', $path);
	}
	
	/**
	 * Run and return the output from an svn `diff`.
	 *
	 * @return string
	 */
	public function diff()
	{
		return implode(PHP_EOL, $this->_runCommand('diff'));
	}
	
	/**
	 * Revert pristine working copy file.
	 *
	 * @param string $path      Path to revert.
	 * @param bool   $recursive Descend recursively when true.
	 *
	 * @return void
	 */
	public function revert($path, $recursive = false)
	{
		if ($recursive) {
			$this->_runCommand('revert', '-R', $path);
		} else {
			$this->_runCommand('revert', $path);
		}
	}
	
	/**
	 * Get the status of the working copy.
	 *
	 * @return array
	 */
	public function status()
	{
		$status = implode(PHP_EOL, $this->_runCommand('status', '--xml', $this->_path));
		
		$xml = simplexml_load_string($status);
		
		$result = array();
		
		foreach ($xml->target as $target) {
			$targetPath = $target['path'];
			
			if ($targetPath == '.') {
				$targetPath = '';
				$offset = 0;
			} else {
				$offset = strlen($targetPath) + 1;
			}
			
			foreach ($target->entry as $entry) {
				$path = substr($entry['path'], $offset);
				$result[] = new Svn_Entry($entry, $path);
			}
		}
		
		return $result;
	}
	
	/**
	 * Get the commit log for the given path.
	 * 
	 * @param string $path Path to get the commit log for, or current directory if nothing passed.
	 * @param array  $revs Only fetch the log for specific revisions if this is passed.
	 * 
	 * @return array
	 */
	public function log($path = null, array $revs = array())
	{
		$args = array('log', '--xml');
		if ($path === null) {
			$path = '.';
		}
		
		$args[] = $path;
		
		foreach ($revs as $rev) {
			$args[] = '-r' . $rev;
		}
		
		$log = new Svn_Log(implode(PHP_EOL, call_user_func_array(array($this, '_runCommand'), $args)));
		return $log->revisions();
	}
	
	/**
	 * List the contents of a working copy (or repo-relative path).
	 * 
	 * Returns an array of filenames.
	 * 
	 * @param string $path Path to list, or current directory if nothing passed.
	 * 
	 * @return array
	 */
	public function ls($path = null)
	{
		if ($path === null) {
			$path = '.';
		}

		return $this->_runCommand('list', $path);
	}
	
	/**
	 * Subversion mergeinfo wrapper command.
	 * 
	 * Returns an array of ints  representing the revisions eligble for merging.
	 * 
	 * @param string $path Path to use as reference.
	 * 
	 * @return array
	 */
	public function eligible($path)
	{
		$revs = $this->_runCommand('mergeinfo', '--show-revs', 'eligible', $path);

		$result = array();

		foreach ($revs as $rev) {
			// Trim off the leading 'r', and cast to int.
			$result[] = (int) substr($rev, 1);
		}

		return $result;
	}
	
	/**
	 * Run a subversion command, and return the result as a string.
	 *
	 * @param string $args Variable number of arguments to pass to subversion.
	 *
	 * @return string
	 */
	protected function _runCommand($args)
	{
		$args = func_get_args();
		
		$cmd = escapeshellcmd('svn');
		
		foreach ($args as $arg) {
			$cmd .= ' ' . escapeshellarg($arg);
		}

		// echo 'Running: ', $cmd, PHP_EOL;

		$output = array();
		$exitCode = null;
		exec($cmd, $output, $exitCode);
		
		if ($exitCode !== 0) {
			throw new Exception('Command "' . $cmd . '" failed: ' . $output);
		}
		
		return $output;
	}
}