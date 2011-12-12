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
		$this->_runCommand('add', $path);
	}
	
	/**
	 * Run and return the output from an svn `diff`.
	 *
	 * @return string
	 */
	public function diff()
	{
		return $this->_runCommand('diff');
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
		$status = $this->_runCommand('status', '--xml', $this->_path);
		
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
		
		$output = array();
		$exitCode = null;
		exec($cmd, $output, $exitCode);
		$output = implode(PHP_EOL, $output);
		
		if ($exitCode !== 0) {
			throw new Exception('Command \'' . $cmd . '\' failed: ' . $output);
		}
		
		return $output;
	}
}