<?php

/**
 * Merge the revisions that are eligible.
 * 
 * Merges revisions from a given branch into the working copy.
 */
class Command_Svneligible_Merge extends Command_Svneligible
{
	/**
	 * Instance of the Svn class to use.
	 * 
	 * @var object
	 */
	protected $_svn;

	/**
	 * Command runner - does the actual work.
	 * 
	 * @return void
	 */
	public function run()
	{
		$this->_svn = new Svn('.');

		$options = $this->_parseOptions();

		echo $options->path, PHP_EOL;

		$eligible = $this->_svn->eligible($options->path);

		if (! (bool) $eligible) {
			echo '    No eligible revisions.', PHP_EOL;
			return;
		}

		// There are revs, filter on author if required.
		if ((bool) $author = CLI::getNamedArgument('author')) {
			$eligible = $this->_filterAuthor($eligible, $author);

			if (! count($eligible)) {
				echo '    There are no eligible revs by author \'', $author, '\'.', PHP_EOL;
				exit(1);
			}
		}

		// Apply range filter.
		$eligible = $this->_filterRange($eligible, $options->initial, $options->final);
		if (! count($eligible)) {
			echo '    There are no eligible revisions within the range specified.', PHP_EOL;
			exit(1);
		}

		$this->_svn->merge($options->path, $eligible);
	}

	/**
	 * Parse the command-line arguments, returning an object.
	 * 
	 * The object returned is guaranteed to have the following properties:
	 *     - path
	 *     - initial
	 *     - final
	 * 
	 * @return object
	 */
	protected function _parseOptions()
	{
		// Initialise the defaults.
		$result = (object) array(
			'path'    => false,
			'initial' => false,
			'final'   => false,
		);

		if (CLI::getNamedArgument('stable')) {
			// The --stable flag means to check against the 'newest' release branch.
			$releases = $this->_svn->ls('^/releases');
			natsort($releases);
			$result->path = '^/releases/' . array_pop($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$result->path = CLI::getUnnamedArgument(1);
		}

		if (! $result->path) {
			echo 'You must specify a path to use the \'merge\' command.', PHP_EOL;
			exit(1);
		}

		// Now parse out the range / limiting options.
		if ((bool) $initial = (int) CLI::getNamedArgument('initial')) {
			$result->initial = $initial;
		}

		if ((bool) $final = (int) CLI::getNamedArgument('final')) {
			$result->final = $final;
		}

		if ((bool) $range = CLI::getNamedArgument('range')) {
			if ($result->initial || $result->final) {
				echo 'You cannot specify both a range and an initial or final revision.', PHP_EOL;
				exit(1);
			}

			$range = explode(':', $range);
			if (count($range) != 2) {
				echo 'You must specify a range in the correct format: --range=<initial>:<final>.', PHP_EOL;
				exit(1);
			}

			$result->initial = (int) $range[0];
			$result->final = (int) $range[1];
		}

		return $result;
	}

	/**
	 * Remove revisions not authored by the specified user.
	 * 
	 * @param array  $revs   Revisions to filter.
	 * @param string $author Username to keep revisions for.
	 * 
	 * @return array
	 */
	protected function _filterAuthor($revs, $author)
	{
		$result = array();

		// Get all the eligible revs' log entries.
		$logs = $this->_svn->log('^/', $revs);

		// Loop over each, adding any where the author matches.
		foreach ($logs as $rev => $log) {
			if ($log->author == $author) {
				$result[] = $rev;
			}
		}

		return $result;
	}

	/**
	 * Remove revisions outside of the specified range.
	 * 
	 * @param array $revs    Revisions to filter.
	 * @param mixed $initial Pass an int to filter out revs prior to this, or false.
	 * @param mixed $final   Pass an int to filter out revs after this, or false.
	 * 
	 * @return array
	 */
	protected function _filterRange($revs, $initial, $final)
	{
		if (! $initial && ! $final) {
			// If neither is set, just return the original array.
			return $revs;
		}

		$result = array();

		foreach ($revs as $rev) {
			if (is_int($initial) && $rev < $initial) {
				// Don't include revs prior to $inital.
				continue;
			}

			if (is_int($final) && $rev > $final) {
				// Don't include revs subsequent to $final.
				continue;
			}

			$result[] = $rev;
		}

		return $result;
	}
}