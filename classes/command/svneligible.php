<?php

/**
 * This is a base class for all the svneligible-related commands to extend.
 */
abstract class Command_Svneligible extends Command
{
	/**
	 * True if this command requires a Subversion working copy to run.
	 *
	 * @var boolean
	 */
	protected $_requiresWorkingCopy = true;

	/**
	 * Instance of the Svn class.
	 *
	 * @var object
	 */
	protected $_svn;

	/**
	 * Constructor.
	 *
	 * @param mixed $args Arguments instance, or null.
	 */
	public function __construct($args = null)
	{
		parent::__construct($args);

		// Note that we *always* operate on the root of the working copy.
		try {
			$this->_svn = new Svn(Svn::getRoot('.'));
		} catch (Exception $e) {
			if ($this->_requiresWorkingCopy) {
				throw $e;
			}
		}

		// Allow subclasses to set up any state they need.
		$this->_setUp();
	}

	/**
	 * Filter out any paths specified in the config file for the current command.
	 *
	 * @param array &$paths The paths array to filter.
	 *
	 * @return void
	 */
	protected function _filter(&$paths)
	{
		// Check for old-style configuration.
		if (Config::get('svneligible.exclude')) {
			throw new Exception_InvalidConfig('You need to update your configuration to remove the \'exclude\' section.');
		}

		// Work out the config key to use based on class name.
		$class = get_class($this);
		$subcommand = strtolower(substr($class, strrpos($class, '_') + 1));

		// Grab the configured ignore patterns.
		$patterns = Config::get('svneligible.' . $subcommand . '.exclude', null);

		if (! $patterns) {
			// Nothing to do if no patterns.
			return;
		}

		foreach ($paths as $idx => $path) {
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $path)) {
					unset($paths[$idx]);
				}
			}
		}
	}

	/**
	 * Get the path specified by the user or upstream/alias configuration.
	 *
	 * A user may input a path as a manually-typed string, use --stable to use
	 * the current release branch, or may have configured upstreams/aliases. This
	 * method will attempt to work out the path requested.
	 *
	 * @param int $index Pass an int to specify the argument index, defaults to 1.
	 *
	 * @return string
	 */
	protected function _getPath($index = 1)
	{
		$path = false;

		if ($this->_args->getNamedArgument('stable')) {
			// The --stable flag means to use against the 'newest' release branch.
			$releases = Command_Svneligible::factory('releases')->run(false);
			$path = array_pop($releases);
		} else {
			// Get the path from unnamed arguments.
			$path = $this->_args->getUnnamedArgument($index);
		}

		if (! $path
			|| ($path && $path[0] != '^')
		) {
			// There's still no path (or it isn't a relative Subversion path). Look for an upstream/alias.
			if (! $path) {
				// Use the working copy checked-out path.
				$upstreamName = $this->_svn->relativePath();
			} else {
				// Use the passed-in name, and unset $path.
				$upstreamName = $path;
				$path = false;
			}

			$upstream = new Upstream('.');
			$path = $upstream->getUpstream($upstreamName);
		}

		if (! $path) {
			throw new Exception('You must specify a path to use the \'' . $this->getName() . '\' command.');
		}

		return $path;
	}

	/**
	 * Default implementation does nothing, but subclasses can use this.
	 *
	 * @return void
	 */
	protected function _setUp()
	{
	}
}
