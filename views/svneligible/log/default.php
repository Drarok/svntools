<?php

/**
 * Simple view for revisions.
 */

echo '    r', $rev, PHP_EOL;

echo '        ', str_pad($log->author, 10), ' | ', $log->date, PHP_EOL;
foreach (explode(PHP_EOL, trim($log->msg)) as $line) {
	echo '        ', $line, PHP_EOL;
}
echo PHP_EOL;

if ($showPaths) {
	foreach ($log->paths as $path) {
		echo '        ', $path->action, ' ', $path->path, PHP_EOL;
	}
}

echo PHP_EOL;