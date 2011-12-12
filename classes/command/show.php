<?php

class Command_Show extends Command
{
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		
		// Grab the id from the command line.
		$id = Cli::getUnnamedArgument(1, null);
		
		// Find its path.
		$path = $stash->getStashPath($id);
		
		// Output a header.
		$name = basename($path, '.diff');
		echo 'Stash \'', $name, '\':', PHP_EOL, PHP_EOL;
		
		// Output the diff.
		echo file_get_contents($path), PHP_EOL;
	}
}