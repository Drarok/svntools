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
	if (is_array($additionalParams) && count($additionalParams)) {
		$params = array_merge($params, $additionalParams);
	}
} else {
	// Grab all the params after our script's file path at 0.
	$params = array_slice($_SERVER['argv'], 1);
}

// Wrap each one in quotes for good luck.
foreach ($params as &$param) {
	$param = '"' . $param . '"';
}

// Build up the shell command.
$shellCommand = 'svn ';
if ($subcommand) {
	$shellCommand .= $subcommand . ' ';
};
$shellCommand .= implode(' ', $params);

// Let's rock this!
echo shell_exec($shellCommand), PHP_EOL;