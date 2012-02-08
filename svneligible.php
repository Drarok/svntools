#!/usr/bin/env php
<?php
/**
 * Svneligible command entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Set up valid commands.
$commands = array(
	'branches' => false,
	'releases' => false,
	'show' => false,
	'help' => false,
);

// Set up default options.
$options = array(
	'show-log' => false,
	'help' => false,
);

// Set up parameters.
$parameters = array();

foreach (array_slice($argv, 1) as $arg) {
	if (substr($arg, 0, 2) == '--') {
		$key = substr($arg, 2);
		$value = true;

		// If there's an equals sign, split on it.
		if (strpos($key, '=') !== false) {
			list($key, $value) = explode('=', $key, 2);
		}

		if (array_key_exists($key, $options)) {
			$options[$key] = $value;
		}
	} else {
		if (array_key_exists($arg, $commands)) {
			$commands[$arg] = true;
		} else {
			$parameters[] = $arg;
		}
	}
}

// If no command specified, run the 'help' command.
if (! $commands['branches'] && ! $commands['releases'] && ! $commands['show']) {
	$commands['help'] = true;
}

if ($options['help'] || $commands['help']) {
	echo 'Usage: svneligible [<options>] [...] [<command>]', PHP_EOL, PHP_EOL;

	$commands_help = array(
		'branches' => '- List the contents of ^/branches (up to 2 levels deep).',
		'releases' => '- List the contents of ^/releases.',
		'show' => '    - Show the eligible revisions from the given branch/release.',
		'help' => '    - This help text.',
	);

	echo 'Available commands:', PHP_EOL;
	foreach ($commands_help as $command => $help) {
		echo '    ', $command, ' ', $help, PHP_EOL;
	}
	echo PHP_EOL;

	$options_help = array(
		'show-log' => '- Display the commit log for eligible revisions.',
	);

	echo 'Available options:', PHP_EOL;
	foreach ($options_help as $option => $help) {
		echo '    --', $option, ' ', $help, PHP_EOL;
	}
	echo PHP_EOL;

	exit(0);
}

if ($commands['branches']) {
	$roots = explode(PHP_EOL, trim(shell_exec('svn ls ^/branches')));
	foreach ($roots as $root) {
		$cmd = 'svn ls ' . escapeshellarg('^/branches/' . $root);
		$svn = trim(shell_exec($cmd));

		if (! $svn) {
			continue;
		}

		$twigs = explode(PHP_EOL, $svn);
		foreach ($twigs as $twig) {
			echo '^/branches/', $root, $twig, PHP_EOL;
		}
	}
}

if ($commands['releases']) {
	$releases = explode(PHP_EOL, trim(shell_exec('svn ls ^/releases')));

	foreach ($releases as $release) {
		echo '^/releases/', $release, PHP_EOL;
	}
}

if ($commands['show']) {
	foreach ($parameters as $param) {
		echo $param, PHP_EOL;

		$cmd = 'svn mergeinfo --show-revs eligible ' . escapeshellarg($param);
		$svn = trim(shell_exec($cmd));

		if (! $svn) {
			echo '    No eligible revisions.', PHP_EOL;
		} else {
			$revs = explode(PHP_EOL, $svn);

			if ($options['show-log']) {
				$cmd = 'svn log --xml';
				foreach ($revs as $rev) {
					$cmd .= ' ' . escapeshellarg('-' . $rev);
				}
				$cmd .= ' ^/';

				$xml = new Svn_Log(shell_exec($cmd));
				$logs = $xml->revisions();
			} else {
				$logs = array();
				foreach ($revs as $rev) {
					$logs[(int) substr($rev, 1)] = null;
				}
			}

			ksort($logs);
			foreach ($logs as $revision => $info) {
					echo '    r', $revision, PHP_EOL;
				if ($info) {
					echo '        ', str_pad($info->author, 10), ' | ', $info->date, PHP_EOL;
					echo '        ', $info->msg, PHP_EOL;
				}
			}
		}
	}
}
