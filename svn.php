#!/usr/bin/env php
<?php
/**
 * Svn tool entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Grab the subcommand.
$subcommand = array_key_exists(1, $_SERVER['argv'])
	? $_SERVER['argv'][1]
	: NULL;

if ($subcommand) {
	// Grab the params after the subcommand.
	$params = array_slice($_SERVER['argv'], 2);

	$additionalParams = Config::get('svn.' . $subcommand);

	if ($additionalParams !== NULL) {
		// If it's a string, it's a command alias.
		while (is_string($additionalParams)) {
			echo 'svn.', $subcommand, ' => svn.' . $additionalParams, PHP_EOL;
			// Remap to the alias.
			$subcommand = $additionalParams;

			// Fetch the aliased options.
			$additionalParams = Config::get('svn.' . $subcommand);
		}

		if (! is_array($additionalParams)) {
			throw new Exception('Invalid configuration found: svn.' . $subcommand);
		} elseif ($additionalParams) {
			$params = array_merge($params, $additionalParams);
		}
	}
} else {
	// Grab all the params after our script's file path at 0.
	$params = array_slice($_SERVER['argv'], 1);
}

Svn_Command::factory($subcommand, $params)
	->run();