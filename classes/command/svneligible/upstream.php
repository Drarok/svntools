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
		$upstreamPath = $this->_args->getUnnamedArgument(1);
		$path = $this->_args->getNamedArgument('path');

		$upstream = new Upstream('.');

		if (! $path
			&& ! $upstreamPath
			&& ! $this->_args->getNamedArgument('remove')
		) {
			// No options passed in, show current config.
			foreach ($upstream->getAllUpstreams() as $path => $upstreamPath) {
				echo $path, ' => ', $upstreamPath, PHP_EOL;
			}
			return;
		}

		if (! (bool) $path) {
			// No path was passed in, so work out the current one.
			$svn = new Svn(Svn::getRoot('.'));
			$path = $svn->relativePath();
		}

		if (! strlen($path) || $path[0] != '^') {
			throw new Exception('Please specify a valid repo-relative path (' . $path . ').');
		}

		if ($this->_args->getNamedArgument('remove')) {
			$previousValue = $upstream->getUpstream($path);

			if ($previousValue === NULL) {
				echo 'Nothing to do, no upstream set for ', $path, PHP_EOL;
				return;
			}

			echo 'Removing upstream for path ', $path, ' (was ', $previousValue, ')', PHP_EOL;
			$upstream->removeUpstream($path);
			return;
		}

		// Validate the new upstream branch.
		if (! strlen($upstreamPath) || $upstreamPath[0] != '^') {
			throw new Exception('Please specify a valid repo-relative upstream path.');
		}

		echo 'Setting upstream to ', $upstreamPath, ' for path ', $path, PHP_EOL;
		$upstream->addUpstream($path, $upstreamPath);
	}
}