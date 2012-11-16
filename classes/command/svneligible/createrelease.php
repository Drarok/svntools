<?php

/**
 * Create a new release branch.
 *
 * Determines the next likely version number, and creates a new release branch
 * after getting confirmation its guess was correct.
 */
class Command_Svneligible_CreateRelease extends Command_Svneligible
{
	/**
	 * Create a new release branch.
	 *
	 * @return void
	 */
	public function run()
	{
		// Grab the latest release branch.
		$releases = Command_Svneligible::factory('releases')->run(false);
		$currentRelease = array_pop($releases);

		// Increment the last part.
		$parts = explode('.', $currentRelease);
		$parts[count($parts) - 1] += 1;
		$newRelease = implode('.', $parts);

		// Suggest that branch to the user, allowing them to override it.
		echo 'Create branch [', $newRelease, ']: ';
		if (($userInput = trim(fgets(STDIN))) != '') {
			$newRelease = $userInput;
		}

		if ($this->_args->getNamedArgument('no-commit')) {
			$commitMessage = null;
		} else {
			$commitMessage = 'Creating new release branch ' . $newRelease;
		}

		// We branch from the current working copy path.
		$existingPath = $this->_svn->relativePath();

		// Let's create the branch!
		$this->_svn->branch($existingPath, $newRelease, $commitMessage);
	}
}