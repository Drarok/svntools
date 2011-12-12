<?php

class Command_Save extends Command
{
	public function run()
	{
		$stashName = Cli::getUnnamedArgument(1);
		
		if (! (bool) $stashName) {
			echo 'You must supply a stash name.', PHP_EOL;
			exit(1);
		}
		
		$path = getcwd();
		$stash = new Stash($path);
		
		$includeUntracked = (bool) Cli::getNamedArgument('untracked-files');
		
		$stash->addStash($stashName, $includeUntracked);
		
		echo 'Created new stash: ', $stashName, PHP_EOL;
	}
}