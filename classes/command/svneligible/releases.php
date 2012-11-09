<?php

/**
 * Releases command for svneligible.
 *
 * Shows all available releases from ^/releases in the working copy.
 */
class Command_Svneligible_Releases extends Command_Svneligible
{
	/**
	 * Prefix to to use for listing.
	 *
	 * @const string
	 */
	const PREFIX = '^/releases';

	/**
	 * Fetch and filter the releases, returning the filtered array.
	 *
	 * @param bool $output When true, output to stdout.
	 *
	 * @return array
	 */
	public function run($output = true)
	{
		$releases = $this->_svn->ls(static::PREFIX);
		$this->_filter($releases);
		natsort($releases);

		// Prefix the releases for display.
		foreach ($releases as &$release) {
			$release = static::PREFIX . '/' . rtrim($release, '/');
			if ($output) {
				echo $release, PHP_EOL;
			}
		}

		return $releases;
	}
}