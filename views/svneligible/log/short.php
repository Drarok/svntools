<?php

/**
 * Short-style view for revisions.
 */

echo 'r', $rev, ' | ', str_pad($log->author, 10), ' | ', implode(' | ', explode(PHP_EOL, trim($log->msg))), PHP_EOL;

if ($showPaths) {
	foreach ($log->paths as $path) {
		echo '        ', $path->action, ' ', $path->path, PHP_EOL;
	}

	echo PHP_EOL;
}