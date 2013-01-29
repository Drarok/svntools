<?php
/**
 * Subversion Status Command.
 *
 * @category Subversion
 * @package  Subversion
 */
class Svn_Command_Status extends Svn_Command
{
	public function run()
	{
		$ignores = $this->_getIgnores();

		if (! $ignores) {
			// No ignores, just pass through.
			$this->_run();
			return;
		}

		// We've got some ignores to apply, let's do this!
		$this->_params[] = '--xml';
		$output = $this->_run(true);
		$xml = simplexml_load_string($output);
		if (! $xml) {
			echo $output, PHP_EOL;
			throw new Exception('Failed to parse XML!');
		}
		foreach ($xml->target->entry as $entry) {
			foreach ($ignores as $ignorePattern) {
				if (fnmatch($ignorePattern, $entry['path'])) {
					// Don't output if it matches the pattern.
					continue;
				}

				// TODO: Match subversions default output format strictly.
			}
			echo $entry['path'], PHP_EOL;
		}
		return;
	}

	/**
	 * Get configured ignores.
	 *
	 * @return array
	 */
	protected function _getIgnores()
	{
		return array(
			'*.tgz',
		);
	}
}