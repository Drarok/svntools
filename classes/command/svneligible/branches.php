<?php

/**
 * Svneligible Branches command.
 * 
 * Lists from ^/branches, up to two levels deep.
 */
class Command_Svneligible_Branches extends Command_Svneligible
{
	/**
	 * Prefix to to use for listing.
	 * 
	 * @const string
	 */
	const PREFIX = '^/branches';

	/**
	 * Command runner - does the actual work.
	 * 
	 * @return void
	 */
	public function run()
	{
		$svn = new Svn('.');

		foreach ($svn->ls(static::PREFIX) as $root) {
			foreach ($svn->ls(static::PREFIX . '/' . $root) as $twig) {
				echo static::PREFIX, '/', $root, rtrim($twig, '/'), PHP_EOL;
			}
		}
	}
}