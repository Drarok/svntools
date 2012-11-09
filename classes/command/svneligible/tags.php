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
		$tags = $this->_svn->ls(static::PREFIX);
		$this->_filter($tags);
		natsort($tags);

		foreach ($tags as $tag) {
			echo static::PREFIX, '/', rtrim($tag, '/'), PHP_EOL;
		}
	}
}