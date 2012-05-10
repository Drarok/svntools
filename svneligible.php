#!/usr/bin/env php
<?php
/**
 * Svneligible tool entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Set up command-line parsing.
$mapping = array(
	'v' => 'verbose',
);
$args = new Arguments(array_slice($_SERVER['argv'], 1), $mapping);

if ($args->getNamedArgument('verbose')) {
	Svn::setDefaultVerbose(true);
}

// Override the command if there are none, or --help passed.
if (! (bool) $command = $args->getUnnamedArgument(0)) {
	$command = 'help';
} elseif ($args->getNamedArgument('help')) {
	$command = 'help';
}

// Despatch the command.
$command = Command_Svneligible::factory(strtolower($command), $args);

if ($command instanceof Command_Svneligible) {
	$command->run();
}
