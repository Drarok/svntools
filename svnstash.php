#!/usr/bin/env php
<?php
/**
 * Svnstash tool entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Initialise the command-line helper.
$mapping = array(
	'v' => 'verbose',
	'u' => 'untracked-files',
	'f' => 'force',
);

$args = new Arguments(array_slice($_SERVER['argv'], 1));

// Use the 1st argument as the command, if there is one.
$command = $args->getUnnamedArgument(0, 'list');

if ($args->getNamedArgument('help')) {
	// Override the command if --help is passed.
	$command = 'help';
}

try {
	$commandInstance = Command_Svnstash::factory($command, $args);
	
	if (! $commandInstance instanceof Command) {
		throw new Exception('Invalid command: ' . $command);
	}
} catch (Exception $e) {
	echo $e->getMessage(), PHP_EOL;
	exit(1);
}

$commandInstance->run();