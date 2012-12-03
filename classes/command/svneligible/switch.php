<?php
/**
 * Switch implementation, adding support for --stable.
 *
 * Switches the working copy to another branch, always from the base of the
 * working copy, never allowing subtree switching.
 */
class Command_Svneligible_Switch extends Command_Svneligible
{
	public function run()
	{
		// Initialise the path var.
		$path = false;

		// Create a stack instance.
		$stack = new Stack(Svn::getRoot('.'), 'branches.stack');

		// Get the path from command line or upstreams.
		try {
			$path = $this->_getPath();
		} catch (Exception $e) {
			// Note that for this command, a missing path isn't an immediate failure,
			// so we ignore the Exception here.
			$path = false;
		}

		if (! $path && $this->_args->getNamedArgument('pop', false)) {
			// There's still no path, but we've been asked to pop from the stack.
			if ($path = $stack->pop()) {
				echo 'Popped ', $path, ' off the branch stack.', PHP_EOL;
			}
		}

		// We've still got no path, so give up.
		if (! $path) {
			throw new Exception('You must specify a path to use the \'' . $this->getName() . '\' command.');
		}

		// Push the current branch onto the stack?
		if ($this->_args->getNamedArgument('push')) {
			$currentBranch = $this->_svn->relativePath();
			$stack->push($currentBranch);
			echo 'Pushed ', $currentBranch, ' onto the branch stack.', PHP_EOL;
		}

		// All the other commands show the branch, so this one will, too.
		echo $path, PHP_EOL;

		// Note that we *always* operate on the root of the working copy.
		$this->_svn->switchTo($path);
	}
}