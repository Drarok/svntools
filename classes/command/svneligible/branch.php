<?php
/**
 * Create a branch and automatically set its upstream.
 */
class Command_Svneligible_Branch extends Command_Svneligible
{
	/**
	 * Run the branch command.
	 * 
	 * @return void
	 */
	public function run()
	{
		if (! (bool) $firstPath = $this->_args->getUnnamedArgument(1)) {
			throw new Exception('You must specify at least one path.');
		}

		$svn = new Svn(Svn::getRoot('.'));

		if ((bool) $secondPath = $this->_args->getUnnamedArgument(2)) {
			// Got a second path, so first is source, second is destination.
			$existingPath = $firstPath;
			$newPath = $secondPath;
		} else {
			// No second path, so the first is the destination, not source.
			$existingPath = $svn->relativePath();
			$newPath = $firstPath;
		}

		$commitMessage = $this->_args->getNamedArgument('commit');
		$createParents = $this->_args->getNamedArgument('parents');

		$switch = ! $this->_args->getNamedArgument('no-switch', false);

		$svn->branch($existingPath, $newPath, $commitMessage, $createParents);

		echo 'Setting upstream to ', $existingPath, ' for path ', $newPath, PHP_EOL;
		$upstream = new Upstream('.');
		$upstream->addUpstream($newPath, $existingPath);

		if ($switch) {
			echo 'Switching working copy to ', $newPath, PHP_EOL;
			$svn->switchTo($newPath);
		} else {
			echo 'Skipping working copy switch.', PHP_EOL;
		}
	}
}