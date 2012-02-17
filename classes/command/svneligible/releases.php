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
	 * Command runner - does the actual work.
	 * 
	 * @return void
	 */
	public function run()
	{
		$svn = new Svn('.');
		$releases = $svn->ls(static::PREFIX);
		natsort($releases);

		foreach ($releases as $release) {
			echo static::PREFIX, '/', rtrim($release, '/'), PHP_EOL;
		}
	}
}