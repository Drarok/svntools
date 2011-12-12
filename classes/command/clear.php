<?php

class Command_Clear extends Command
{
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		
		if (! Cli::getNamedArgument('force', false)) {
			echo 'Are you sure you want to remove all stashes [y/N]? ';;
			
			$input = strtolower(trim(fgets(STDIN)));
			
			if ($input !== 'y') {
				exit;
			}
		}
		
		$stash->removeAllStashes();
	}
}