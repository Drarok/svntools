#!/usr/bin/env php
<?php
/**
 * Svnstash command entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Initialise the command-line helper.
CLI::init(array(
	'v' => 'verbose',
	'u' => 'untracked-files',
	'f' => 'force',
));

// Use the 1st argument as the command, if there is any.
$command = CLI::getUnnamedArgument(0, 'list');

if (CLI::getNamedArgument('help')) {
	// Override the command if --help is passed.
	$command = 'help';
}

try {
	$commandInstance = Command_Svnstash::factory($command);
	
	if (! $commandInstance instanceof Command) {
		throw new Exception('Invalid command: ' . $command);
	}
} catch (Exception $e) {
	echo $e->getMessage(), PHP_EOL;
	exit(1);
}

$commandInstance->run();