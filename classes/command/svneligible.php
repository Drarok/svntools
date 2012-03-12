<?php

/**
 * This is a base class for all the svneligible-related commands to extend.
 */
abstract class Command_Svneligible extends Command
{
	/**
	 * Filter out any paths specified in the config file for the current command.
	 * 
	 * @param array &$paths The paths array to filter.
	 * 
	 * @return void
	 */
	protected function _filter(&$paths)
	{
		// Work out the config key to use based on class name.
		$class = get_class($this);
		$key = strtolower(substr($class, strrpos($class, '_') + 1));

		// Grab the configured ignore patterns.
		$patterns = Config::get('svneligible.exclude.' . $key, null);

		if (! $patterns) {
			// Nothing to do if no patterns.
			return;
		}

		foreach ($paths as $idx => $path) {
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $path)) {
					unset($paths[$idx]);
				}
			}
		}
	}
}
