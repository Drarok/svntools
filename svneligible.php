#!/usr/bin/env php
<?php
/**
 * Svneligible tool entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Set up command-line parsing.
$mapping = array(
	'v' => 'verbose',
	'r' => 'reverse',
);
$args = new Arguments(array_slice($_SERVER['argv'], 1), $mapping);

if ($args->getNamedArgument('verbose')) {
	Svn::setDefaultVerbose(true);
}

// Get the command, if any.
$command = $args->getUnnamedArgument(0);
$originalCommand = false;

// Override the command if --help passed, or no command.
if ($args->getNamedArgument('help') || ! $command) {
	$originalCommand = $command;
	$command = 'help';

	// Rebuild the arguments to force 'help [command]' format.
	$argsArray = array($command);
	if ((bool) $originalCommand) {
		$argsArray[] = $originalCommand;
	}

	$args = new Arguments($argsArray, $mapping);
}

// Set debug mode, if requested.
Handler::setDebug($args->getNamedArgument('debug', false));

// Despatch the command.
$command = Command_Svneligible::factory(strtolower($command), $args);

if ($command instanceof Command_Svneligible) {
	$command->run();
}