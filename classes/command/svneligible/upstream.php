<?php
/**
 * Set the "upstream" branch, so the path may be omitted for 'show' and 'merge'.
 */
class Command_Svneligible_Upstream extends Command_Svneligible
{
	/**
	 * Upstream object used for operations.
	 *
	 * @var Upstream
	 */
	protected $_upstream;

	/**
	 * This is the main entrypoint to the command.
	 *
	 * @return void
	 */
	public function run()
	{
		// Don't forget that argument 0 is the command).
		$subCommand = $this->_args->getUnnamedArgument(1);

		$validSubCommands = array('', 'list', 'set', 'remove', 'cleanup');
		if (! in_array($subCommand, $validSubCommands)) {
			echo 'Error: Unknown subcommand \'', $subCommand, '\'.', PHP_EOL;
			return;
		}

		$this->_upstream = new Upstream('.');

		switch ($subCommand) {
			case '':
			case 'list':
				$this->_listUpstreams();
				break;

			case 'set':
				$upstreamPath = $this->_args->getUnnamedArgument(2);
				$pathOrAlias = $this->_args->getUnnamedArgument(3);
				$this->_setUpstream($pathOrAlias, $upstreamPath);
				break;

			case 'remove':
				if ($this->_args->getNamedArgument('all', false)) {
					$this->_removeAllUpstreams();
				} else {
					if (! $pathOrAlias = $this->_args->getUnnamedArgument(2)) {
						$pathOrAlias = $this->_svn->relativePath();
					}
					$this->_removeUpstream($pathOrAlias);
				}
				break;

			case 'cleanup':
				$this->_cleanup();
				break;

			default:
				// We should never reach this label.
				echo 'Unexpected subcommand: ', $subCommand, PHP_EOL;
				break;
		}
	}

	/**
	 * List all the upstreams.
	 *
	 * @return void
	 */
	protected function _listUpstreams()
	{
		$currentPath = $this->_svn->relativePath();
		foreach ($this->_upstream->getAllUpstreams() as $alias => $upstreamPath) {
			$this->_outputUpstream($alias, $upstreamPath, $currentPath);
		}
	}

	/**
	 * Set an upstream for the given path or alias.
	 *
	 * @param string $pathOrAlias  Path or alias.
	 * @param string $upstreamPath Upstream path.
	 *
	 * @return void
	 */
	protected function _setUpstream($pathOrAlias, $upstreamPath)
	{
		$previousValue = $this->_upstream->getUpstream($pathOrAlias);

		// Validate the new upstream branch.
		if (! strlen($upstreamPath) || $upstreamPath[0] != '^') {
			throw new Exception('Please specify a valid repo-relative upstream path.');
		}

		if ($previousValue == $upstreamPath) {
			echo 'Nothing to do, upstream is already set to \'', $upstreamPath,
				'\' for \'', $pathOrAlias, '\'.', PHP_EOL;
			return;
		}

		echo 'Setting upstream to \'', $upstreamPath, '\' for \'', $pathOrAlias, '\'';
		if ($previousValue) {
			echo ' (was ', $previousValue, ')';
		}
		echo '.', PHP_EOL;

		$this->_upstream->addUpstream($pathOrAlias, $upstreamPath);
	}

	/**
	 * Removes all upstreams, and outputs the state.
	 *
	 * @return void
	 */
	protected function _removeAllUpstreams()
	{
		$previousUpstreams = $this->_upstream->getAllUpstreams();
		$this->_upstream->removeAllUpstreams();

		echo 'The following upstreams were removed: ', PHP_EOL;
		foreach ($previousUpstreams as $alias => $upstreamPath) {
			echo $alias, ' => ', $upstreamPath, PHP_EOL;
		}
	}

	/**
	 * Remove a single upstream.
	 *
	 * @param string $pathOrAlias Path or alias.
	 *
	 * @return void
	 */
	protected function _removeUpstream($pathOrAlias)
	{
		$previousValue = $this->_upstream->getUpstream($pathOrAlias);

		if ($previousValue === NULL) {
			echo 'Nothing to do, no upstream set for \'', $pathOrAlias, '\'.', PHP_EOL;
			return;
		}

		echo 'Removing upstream for \'', $pathOrAlias, '\' (was ', $previousValue, ')', PHP_EOL;
		$this->_upstream->removeUpstream($pathOrAlias);
	}

	/**
	 * Remove any upstreams set on branches that no longer exist.
	 *
	 * @return void
	 */
	protected function _cleanup()
	{
		$previousUpstreams = $this->_upstream->getAllUpstreams();
	}

	/**
	 * Output upstream info, colorized on non-Windows systems.
	 *
	 * @param string $alias        The alias or change name for the upstream.
	 * @param string $upstreamPath The actual upstream path.
	 * @param string $currentPath  The currently-checked-out branch.
	 *
	 * @return void
	 */
	protected function _outputUpstream($alias, $upstreamPath, $currentPath)
	{
		$color = '';

		if ($alias == $currentPath) {
			// Current branch is always green (and always a branch, no need to check).
			$prefix = '* ';
			$color = ANSI::color(ANSI::GREEN);
		} else {
			// Work out the type of upstream this is, either a branch or and alias.
			$prefix = '  ';

			if ($alias && $alias[0] != '^') {
				// This is an alias upstream, colorize it.
				$color = ANSI::color(ANSI::RED);
			}
		}

		echo $prefix, $color, $alias, ' => ', $upstreamPath, ANSI::reset(), PHP_EOL;
	}
}