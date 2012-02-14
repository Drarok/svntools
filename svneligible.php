#!/usr/bin/env php
<?php
/**
 * Svneligible tool entrypoint.
 */

require_once __DIR__ . '/bootstrap.php';

// Initialise the command-line helper.
CLI::init();

// Set up parameters.
$parameters = array();

// Override the command if there are none, or --help passed.
if (! (bool) $commands = CLI::getUnnamedArguments()) {
	$commands = array('help');
} elseif (CLI::getNamedArgument('help')) {
	$commands = array('help');
}

// Despatch the commands.
foreach ($commands as $command) {
	$command = Command_Svneligible::factory(strtolower($command));

	if ($command instanceof Command_Svneligible) {
		$command->run();
	}
}

die('Finished!' . PHP_EOL);

// If no command specified, run the 'help' command.
if (! $commands['branches'] && ! $commands['releases'] && ! $commands['tags'] && ! $commands['show']) {
	$commands['help'] = true;
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
			echo '^/branches/', $root, rtrim($twig, '/'), PHP_EOL;
		}
	}
}

if ($commands['releases']) {
	$releases = explode(PHP_EOL, trim(shell_exec('svn ls ^/releases')));

	foreach ($releases as $release) {
		echo '^/releases/', rtrim($release, '/'), PHP_EOL;
	}
}

if ($commands['tags']) {
	$tags = explode(PHP_EOL, trim(shell_exec('svn ls ^/tags')));

	foreach ($tags as $tag) {
		echo '^/tags/', rtrim($tag, '/'), PHP_EOL;
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
					foreach (explode(PHP_EOL, trim($info->msg)) as $line) {
						echo '        ', $line, PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}
	}
}
