<?php
/**
 * Apply command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Apply command - apply a stashed set of change to the working copy.
 */
class Command_Svnstash_Apply extends Command
{
	/**
	 * Run the apply command.
	 *
	 * @return void
	 *
	 * @throws Exception When application of the changes failed.
	 */
	public function run()
	{
		// Get the current path.
		$path = getcwd();
		
		// Create a stash instance.
		$stash = new Stash($path);
		
		// Grab the id from the command line.
		$id = CLI::getUnnamedArgument(1, null);
		
		// Get the stash file path and name.
		$path = $stash->getStashPath($id);
		$name = basename($path, '.diff');
		
		echo 'Applying changes from stash \'' . $name . '\'.', PHP_EOL;
		
		$cmd = escapeshellcmd('patch') . ' 2>&1';
		$cmd .= ' ' . escapeshellarg('-p0');
		$cmd .= ' < ' . escapeshellarg($path);
		
		$output = array();
		$exitCode = null;
		exec($cmd, $output, $exitCode);
		$output = implode(PHP_EOL, $output);
		
		if ($exitCode !== 0) {
			throw new Exception('Failed to apply changes: ' . $output);
		}
	}
}