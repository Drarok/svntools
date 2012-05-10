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
		$grandparent = realpath($path);
		
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
	 * Output verbose information when running?
	 * 
	 * @var bool
	 */
	protected $_verbose = FALSE;
	
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
	 * Setter for the _verbose property.
	 * 
	 * @param bool $verbose True to enable verbose mode, or false to disable.
	 * 
	 * @return void
	 */
	public function setVerbose($verbose)
	{
		$this->_verbose = (bool) $verbose;
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
	 * Fetches the commit log, returning an associative array, keyed on the 
	 * revision id (and sorted, too).
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
		
		$method = array($this, '_runCommand');
		$output = call_user_func_array($method, $args);
		$log = new Svn_Log(implode(PHP_EOL, $output));
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
	 * Returns an array of ints representing the revisions eligble for merging.
	 * 
	 * @param string $path   Path to use as reference.
	 * @param string $wcPath Working copy path to check against (defaults to the instance path).
	 * 
	 * @return array
	 */
	public function eligible($path, $wcPath = NULL)
	{
		if ($wcPath === NULL) {
			$wcPath = $this->_path;
		}

		$revs = $this->_runCommand('mergeinfo', '--show-revs', 'eligible', $path, $wcPath);

		$result = array();

		foreach ($revs as $rev) {
			// Trim off the leading 'r', and cast to int.
			$result[] = (int) substr($rev, 1);
		}

		sort($result);

		return $result;
	}

	/**
	 * Subversion merge wrapper command.
	 * 
	 * @param string $path   Path to merge from.
	 * @param mixed  $revs   Array of revision ids to merge, or null for all.
	 * @param string $wcPath Working copy path to merge into (defaults to the instance path).
	 * 
	 * @return void
	 */
	public function merge($path, $revs = null, $wcPath = NULL)
	{
		if ($wcPath === NULL) {
			$wcPath = $this->_path;
		}

		$args = array('merge');

		if ((bool) $revs) {
			$args[] = '-c' . implode(',', $revs);
		}

		$args[] = $path;

		$method = array($this, '_runCommand');
		call_user_func_array($method, $args);
	}

	/**
	 * Get information about the working copy.
	 * 
	 * @return object
	 */
	public function info($path = NULL)
	{
		if ($path === NULL) {
			$path = $this->_path;
		}
		$xmlString = implode(PHP_EOL, $this->_runCommand('info', '--xml', $path));
		return simplexml_load_string($xmlString);
	}

	/**
	 * Get the current working directory's repo-relative path.
	 * 
	 * @param string $path Path to get repo-relative path for (defaults to instance path).
	 * 
	 * @return string
	 */
	public function relativePath($path = NULL)
	{
		if ($path === NULL) {
			$path = $this->_path;
		}

		$info = $this->info();

		$repoRoot = $info->entry->repository->root;
		$pathUrl = $info->entry->url;
		return '^' . substr($pathUrl, strlen($repoRoot));
	}

	/**
	 * Run a subversion command, and return the result as an array of strings.
	 *
	 * @param string $args Variable number of arguments to pass to subversion.
	 *
	 * @return array
	 */
	protected function _runCommand($args)
	{
		$args = func_get_args();
		
		$cmd = escapeshellcmd('svn');
		
		foreach ($args as $arg) {
			$cmd .= ' ' . escapeshellarg($arg);
		}

		if ($this->_verbose) {
			echo 'Running: ', $cmd, PHP_EOL;
		}

		$output = array();
		$exitCode = null;
		exec($cmd, $output, $exitCode);
		
		if ($exitCode !== 0) {
			throw new Exception('Command "' . $cmd . '" failed: ' . $output);
		}
		
		return $output;
	}
}