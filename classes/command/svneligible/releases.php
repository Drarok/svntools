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

		foreach ($svn->ls(static::PREFIX) as $release) {
			echo static::PREFIX, '/', rtrim($release, '/'), PHP_EOL;
		}
	}
}