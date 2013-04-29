<?php
/**
 * Set the "upstream" branch, so the path may be omitted for 'show' and 'merge'.
 */
class Command_Svneligible_Upstream extends Command_Svneligible
{
	/**
	 * This is the main entrypoint to the command.
	 *
	 * @return void
	 */
	public function run()
	{
		// Don't forget that argument 0 is the command).
		$subCommand = $this->_args->getUnnamedArgument(1);

		$validSubCommands = array('', 'set', 'remove');
		if (! in_array($subCommand, $validSubCommands)) {
			echo 'Error: Unknown subcommand \'', $subCommand, '\'.', PHP_EOL;
			return;
		}

		$upstream = new Upstream('.');

		if (! $subCommand) {
			// No options passed in, show current config.
			$currentPath = $this->_svn->relativePath();
			foreach ($upstream->getAllUpstreams() as $alias => $upstreamPath) {
				$this->_outputUpstream($alias, $upstreamPath, $currentPath);
			}

			return;
		}

		if ($subCommand == 'set') {
			$upstreamPath = $this->_args->getUnnamedArgument(2);
			$pathOrAlias = $this->_args->getUnnamedArgument(3);
		} elseif ($subCommand == 'remove') {
			// The 'remove' command only needs the path/alias, so there is no upstreamPath.
			$pathOrAlias = $this->_args->getUnnamedArgument(2);
		}

		if (! (bool) $pathOrAlias) {
			// No path/alias was passed in, nor --all, so use the current path.
			$pathOrAlias = $this->_svn->relativePath();
		}

		$previousValue = $upstream->getUpstream($pathOrAlias);

		if ($subCommand == 'remove') {
			// Are they planning to remove all upstreams?
			if ($this->_args->getNamedArgument('all', false)) {
				$previousUpstreams = $upstream->getAllUpstreams();
				$upstream->removeAllUpstreams();

				echo 'The following upstreams were removed: ', PHP_EOL;
				foreach ($previousUpstreams as $alias => $upstreamPath) {
					echo $alias, ' => ', $upstreamPath, PHP_EOL;
				}

				return;
			}

			if ($previousValue === NULL) {
				echo 'Nothing to do, no upstream set for \'', $pathOrAlias, '\'.', PHP_EOL;
				return;
			}

			echo 'Removing upstream for \'', $pathOrAlias, '\' (was ', $previousValue, ')', PHP_EOL;
			$upstream->removeUpstream($pathOrAlias);
			return;
		}

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

		$upstream->addUpstream($pathOrAlias, $upstreamPath);
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