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
		$branches = $this->_svn->ls(static::PREFIX);
		$this->_filter($branches);
		natsort($branches);

		foreach ($branches as $root) {
			$kindling = $this->_svn->ls(static::PREFIX . '/' . $root);
			natsort($kindling);

			foreach ($kindling as $twig) {
				echo static::PREFIX, '/', $root, rtrim($twig, '/'), PHP_EOL;
			}
		}
	}
}