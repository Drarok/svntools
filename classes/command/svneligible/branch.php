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
		if (! (bool) $existingPath = $this->_args->getUnnamedArgument(1)) {
			throw new Exception('You must specify an existing path.');
		}

		if (! (bool) $newPath = $this->_args->getUnnamedArgument(2)) {
			throw new Exception('You must specify the new path.');
		}

		$commitMessage = $this->_args->getNamedArgument('commit');
		$createParents = $this->_args->getNamedArgument('parents');

		$switch = ! $this->_args->getNamedArgument('no-switch', false);

		$svn = new Svn(Svn::getRoot('.'));
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