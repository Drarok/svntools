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
	 * Instances will use this value in their constructor to set their own _verbose property.
	 *
	 * @var bool
	 */
	static protected $_defaultVerbose = false;

	/**
	 * Instances, keyed on their path.
	 *
	 * @var array
	 */
	static protected $_instances = array();

	/**
	 * Instance getter.
	 *
	 * @param string $path Path to get an instance for.
	 *
	 * @return Svn
	 */
	static public function instance($path)
	{
		if (array_key_exists($path, static::$_instances)) {
			return static::$_instances[$path];
		} else {
			return static::$_instances[$path] = new static($path);
		}
	}

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
		if (! (bool) $realPath = realpath($path)) {
			throw new Exception('Failed to calculate real path for ' . $path);
		}
		return static::instance($realPath)->rootPath();
	}


	/**
	 * Setter for the static $_defaultVerbose property.
	 *
	 * @param bool $verbose Pass true to enable verbose mode by default.
	 *
	 * @return void
	 */
	static public function setDefaultVerbose($verbose)
	{
		static::$_defaultVerbose = (bool) $verbose;
	}

	/**
	 * Stores the path to the working copy we're working on.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Cached data.
	 *
	 * @var Cache_Memory
	 */
	protected $_cache;

	/**
	 * Output verbose information when running or not.
	 *
	 * @var bool
	 */
	protected $_verbose;

	/**
	 * Constructor.
	 *
	 * @param string $path Path to the working copy.
	 */
	protected function __construct($path)
	{
		$this->_path = $path;
		$this->_cache = new Cache_Memory();
		$this->_verbose = static::$_defaultVerbose;
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
	 * @param mixed $upstreamPath Path of the upstream branch to diff against, or null to diff the working copy.
	 * @param mixed $branchPath   Path to compare against the upstream, or null to diff the working copy.
	 * @param array $params       Extra parameters to pass to the diff command.
	 *
	 * @return string
	 */
	public function diff($upstreamPath = null, $branchPath = null, $params = array())
	{
		if ($upstreamPath === null && $branchPath === null) {
			return implode(PHP_EOL, $this->_runCommand('diff'));
		} elseif ($upstreamPath === null || $branchPath === null) {
			throw new Exception('You must path both the upstream and branch you want to diff.');
		} else {
			// Prepend the diff command.
			array_unshift($params, 'diff');

			// Add the branch parts.
			$params[] = $upstreamPath;
			$params[] = $branchPath;

			// Run it.
			return implode(PHP_EOL, call_user_func_array(array($this, '_runCommand'), $params));
		}
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
	 * @return Svn_Entry_Iterator
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

		return new Svn_Entry_Iterator($result);
	}

	/**
	 * Get the commit log for the given path.
	 *
	 * Fetches the commit log, returning an associative array, keyed on the
	 * revision id (and sorted, too).
	 *
	 * @param string $path    Path to get the commit log for, or current directory if nothing passed.
	 * @param mixed  $revs    Only fetch the log for specific revisions if this is passed, pass an array of ints, or a valid string.
	 * @param bool   $verbose Pass the --verbose flag to Subversion.
	 *
	 * @return array
	 */
	public function log($path = null, $revs = null, $verbose = false)
	{
		$args = array('log', '--xml');

		if ((bool) $verbose) {
			$args[] = '--verbose';
		}

		if ($path === null) {
			$path = '.';
		}

		$args[] = $path;

		foreach ((array) $revs as $rev) {
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
	 * Update the working copy.
	 *
	 * @param array $params Extra parameters to pass to svn.
	 *
	 * @return void
	 */
	public function update($params = array())
	{
		array_unshift($params, 'update');
		call_user_func_array(array($this, '_runCommand'), $params);
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
	 * @param string $path        Path to merge from.
	 * @param mixed  $revs        Array of revision ids to merge, or null for all.
	 * @param string $wcPath      Working copy path to merge into (defaults to the instance path).
	 * @param bool   $reintegrate Pass true to perform a 'reintegrate' merge.
	 * @param bool   $recordOnly  Pass true to perform a 'record only' merge.
	 *
	 * @return void
	 */
	public function merge($path, $revs = null, $wcPath = null, $reintegrate = false, $recordOnly = false)
	{
		if ($wcPath === NULL) {
			$wcPath = $this->_path;
		}

		$args = array('merge');

		// Subversion 1.8 deprecates the use of --reintegrate, so only pass it on for older clients.
		if ($reintegrate && version_compare($this->getVersion(), '1.8.0', '<')) {
			$args[] = '--reintegrate';
		}

		if ($recordOnly) {
			$args[] = '--record-only';
		}

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
		$info = $this->info($path);

		$repoRoot = $info->entry->repository->root;
		$pathUrl = $info->entry->url;
		return '^' . substr($pathUrl, strlen($repoRoot));
	}

	/**
	 * Get the root path of a working copy.
	 *
	 * @param string $path Path to a working copy, defaults to instance path.
	 *
	 * @return string
	 */
	public function rootPath($path = NULL)
	{
		$info = $this->info($path);
		return (string) $info->entry->{'wc-info'}->{'wcroot-abspath'};
	}

	/**
	 * Switch the working copy to another URL.
	 *
	 * Named switchTo because you can't have a method named 'switch' in PHP.
	 *
	 * @param string $url URL to switch to.
	 *
	 * @return void
	 */
	public function switchTo($url)
	{
		$this->_runCommand('switch', $url);
	}

	/**
	 * Perform a Subversion commit.
	 *
	 * @param mixed $message A string to use as the commit message, or null to show the editor.
	 *
	 * @return void
	 */
	public function commit($message = null)
	{
		$args = array('commit');

		if ((bool) $message) {
			$args[] = '-m';
			$args[] = $message;
		}

		$method = array($this, '_runCommand');
		call_user_func_array($method, $args);
	}

	/**
	 * Remove a file or directory, optionally with a commit message.
	 *
	 * @param string $path    Path to remove.
	 * @param mixed  $message Commit message to specify.
	 *
	 * @return void
	 */
	public function rm($path, $message = null)
	{
		$args = array('rm', $path);

		if ((bool) $message) {
			$args[] = '-m';
			$args[] = $message;
		}

		$method = array($this, '_runCommand');
		call_user_func_array($method, $args);
	}

	/**
	 * Create a new branch in the repository.
	 *
	 * @param string $existingPath  Path in the repository to branch from.
	 * @param string $newPath       New path in the repository to copy to.
	 * @param mixed  $commitMessage Optional commit message to set for the branch operation.
	 * @param mixed  $createParents Optional. Create parent directories if required?
	 *
	 * @return void
	 */
	public function branch($existingPath, $newPath, $commitMessage = null, $createParents = false)
	{
		$method = array($this, '_runCommand');
		$args = array('cp', $existingPath, $newPath);

		if ((bool) $commitMessage) {
			$args[] = '-m';
			$args[] = $commitMessage;
		}

		if ((bool) $createParents) {
			$args[] = '--parents';
		}

		call_user_func_array($method, $args);
	}

	/**
	 * Return true if the working copy is "dirty" - that is, it has uncommitted changes.
	 *
	 * @return boolean
	 */
	public function isDirty()
	{
		return $this->status()
			->getEntriesInStates(Svn_Entry::ADDED, Svn_Entry::MODIFIED, Svn_Entry::MISSING)
			->count() !== 0;
	}

	/**
	 * Get the version number string.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		static $versionString = null;
		if ($versionString !== null) {
			return $versionString;
		}

		$output = $this->_runCommand('--version');
		if (! isset($output[0])) {
			throw new Exception('Failed to get Subversion version information.');
		}

		if (! preg_match('/svn, version ([0-9]+\\.[0-9]+\\.[0-9]+) \\(r[0-9]+\\)/', $output[0], $matches)) {
			throw new Exception('Unexpected version information format: ' . $output[0]);
		}

		return $versionString = $matches[1];
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

		// Attempt to use the cache to get the answer first.
		if ($cached = $this->_getCached($args)) {
			return $cached;
		}

		$cmd = escapeshellcmd('svn');

		foreach ($args as $arg) {
			$cmd .= ' ' . escapeshellarg($arg);
		}

		// Capture stderr, too.
		$cmd .= ' 2>&1';

		if ($this->_verbose) {
			echo 'Running: ', $cmd, PHP_EOL;
		}

		$output = array();
		$exitCode = null;
		exec($cmd, $output, $exitCode);

		if ($exitCode !== 0) {
			$output = implode(PHP_EOL, $output);
			throw new Exception('Command "' . $cmd . '" failed with exit code ' . $exitCode . ': ' . $output);
		}

		// Cache for later?
		if ((bool) $cacheKey = $this->_getCacheKey($args)) {
			$this->_cache->set($this->_getCacheKey($args), $output);
		}

		return $output;
	}

	/**
	 * Get a cached response for the given args, or FALSE if there isn't one.
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return mixed
	 */
	protected function _getCached($args)
	{
		if (! is_array($args) || ! count($args)) {
			return FALSE;
		}

		if (! (bool) $cacheKey = $this->_getCacheKey($args)) {
			return FALSE;
		} else {
			return $this->_cache->get($this->_getCacheKey($args), FALSE);
		}
	}

	/**
	 * Standardised cache key generation, returns a key, or FALSE if the result shouldn't be cached.
	 *
	 * @param array $args Array of args to turn into a cache key.
	 *
	 * @return mixed
	 */
	protected function _getCacheKey($args)
	{
		static $cacheableCommands = NULL;
		if ($cacheableCommands === NULL) {
			$cacheableCommands = array_fill_keys(array('info', 'list'), true);
		}

		if (! $args[0] || ! array_key_exists($args[0], $cacheableCommands)) {
			return FALSE;
		}

		$cacheKey = '';
		foreach ($args as $arg) {
			$cacheKey .= $arg;
		}

		return hash('sha512', $cacheKey);
	}
}