#!/usr/bin/env php
<?php
/**
 * Svneligible tool entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Initialise the command-line helper.
CLI::init(array(
	'v' => 'verbose',
));

// Override the command if there are none, or --help passed.
if (! (bool) $command = CLI::getUnnamedArgument(0)) {
	$command = 'help';
} elseif (CLI::getNamedArgument('help')) {
	$command = 'help';
}

// Despatch the command.
$command = Command_Svneligible::factory(strtolower($command));

if ($command instanceof Command_Svneligible) {
	$command->run();
}
