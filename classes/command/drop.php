<?php

class Command_Drop extends Command
{
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		
		// Grab the id from the command line.
		$id = Cli::getUnnamedArgument(1, null);
		
		// Remove it.
		$name = $stash->removeStash($id);
		
		echo 'Removed stash \'', $name, '\'.', PHP_EOL;
	}
}