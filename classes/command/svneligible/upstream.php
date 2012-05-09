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
		$upstream = CLI::getUnnamedArgument(1);
		$path = CLI::getNamedArgument('path');

		$stash = new Stash('.');

		if (! $path && ! $upstream) {
			// No options passed in, show current config.
			foreach ($stash->getAllUpstreams() as $path => $upstream) {
				echo $path, ' => ', $upstream, PHP_EOL;
			}
			return;
		}

		if (! (bool) $path) {
			// No path was passed in, so work out the current one.
			$svn = new Svn(Svn::getRoot('.'));
			$path = $svn->relativePath();
		}

		if (! strlen($path) || $path[0] != '^') {
			throw new Exception('Please specify a valid repo-relative path.');
		}

		// Validate the new upstream branch.
		if (! strlen($upstream) || $upstream[0] != '^') {
			throw new Exception('Please specify a valid repo-relative upstream path.');
		}

		if (CLI::getNamedArgument('remove')) {
			$previousValue = $stash->getUpstream($path);

			if ($previousValue === NULL) {
				echo 'Nothing to do, no upstream set for ', $path, PHP_EOL;
				return;
			}

			echo 'Removing upstream for path ', $path, ' (was ', $previousValue, ')', PHP_EOL;
			$stash->removeUpstream($path);
			return;
		}

		echo 'Setting upstream to ', $upstream, ' for path ', $path, PHP_EOL;
		$stash->addUpstream($path, $upstream);
	}
}