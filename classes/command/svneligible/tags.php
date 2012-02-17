<?php

/**
 * Tags command for svneligible.
 * 
 * Shows all available tags from ^/tags in the working copy.
 */
class Command_Svneligible_Tags extends Command_Svneligible
{
	/**
	 * Prefix to to use for listing.
	 * 
	 * @const string
	 */
	const PREFIX = '^/tags';

	/**
	 * Command runner - does the actual work.
	 * 
	 * @return void
	 */
	public function run()
	{
		$svn = new Svn('.');
		$tags = $svn->ls(static::PREFIX);
		natsort($tags);

		foreach ($tags as $tag) {
			echo static::PREFIX, '/', rtrim($tag, '/'), PHP_EOL;
		}
	}
}