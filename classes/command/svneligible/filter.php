<?php

/**
 * This is where common code for the 'merge' and 'show' commands lives.
 */
abstract class Command_Svneligible_Filter extends Command_Svneligible
{
	/**
	 * Instance of the Svn class to use for operations.
	 * 
	 * @var object
	 */
	protected $_svn;

	/**
	 * Parsed options from _parseOptions.
	 * 
	 * @var object
	 */
	protected $_options;

	/**
	 * This method sets up the object ready for the _run method in the concrete class to do the work.
	 * 
	 * @return void
	 */
	public function run()
	{
		// Note that we *always* operate on the root of the working copy.
		$this->_svn = new Svn(Svn::getRoot('.'));

		// This method will throw if there are invalid parameters.
		$this->_parseOptions();

		echo $this->_options->path, PHP_EOL;

		// Get all valid revisions.
		try {
			$revs = $this->_getFilteredEligibleRevisions();
		} catch (Exception $e) {
			echo '    ', $e->getMessage(), PHP_EOL;
			exit(1);
		}

		$this->_run($revs);
	}

	/**
	 * Parse the command arguments, storing them in $this->_options.
	 * 
	 * The object generated is guaranteed to have the following properties:
	 *     - path
	 *     - author
	 *     - initial
	 *     - final
	 * 
	 * @return void
	 */
	protected function _parseOptions()
	{
		// Initialise the defaults.
		$result = (object) array(
			'path'    => false,
			'author'  => false,
			'initial' => false,
			'final'   => false,
		);

		if ($this->_args->getNamedArgument('stable')) {
			// The --stable flag means to check against the 'newest' release branch.
			$releases = Command_Svneligible::factory('releases')->run(false);
			$result->path = array_pop($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$result->path = $this->_args->getUnnamedArgument(1);
		}

		if (! $result->path) {
			// There's still no path. Look for an upstream.
			$upstream = new Upstream('.');
			$result->path = $upstream->getUpstream($this->_svn->relativePath());
		}

		if (! $result->path) {
			throw new Exception('You must specify a path to use the \'' . $this->getName() . '\' command.');
		}

		if ((bool) $author = $this->_args->getNamedArgument('author')) {
			$result->author = $author;
		}

		// Now parse out the range / limiting options.
		if ((bool) $initial = (int) $this->_args->getNamedArgument('initial')) {
			$result->initial = $initial;
		}

		if ((bool) $final = (int) $this->_args->getNamedArgument('final')) {
			$result->final = $final;
		}

		if ((bool) $range = $this->_args->getNamedArgument('range')) {
			if ($result->initial || $result->final) {
				throw new Exception('You cannot specify both a range and an initial or final revision.');
			}

			$range = explode(':', $range);
			if (count($range) != 2) {
				throw new Exception('You must specify a range in the correct format: --range=<initial>:<final>.');
			}

			$result->initial = (int) $range[0];
			$result->final = (int) $range[1];
		}

		$this->_options = $result;
	}

	/**
	 * Fetch all eiligible revisions for the given path, filter them and return.
	 * 
	 * @return array
	 */
	protected function _getFilteredEligibleRevisions()
	{
		// Get all eligible revisions.
		$revs = $this->_svn->eligible($this->_options->path);

		if (! (bool) $revs) {
			throw new Exception('No eligible revisions.');
		}

		// Filter on author.
		if (! (bool) $revs = $this->_filterAuthor($revs, $this->_options->author)) {
			throw new Exception('There are no eligible revisions by author \'' . $this->_options->author . '\'');
		}

		// Filter on range.
		if (! (bool) $revs = $this->_filterRange($revs, $this->_options->initial, $this->_options->final)) {
			throw new Exception('There are no eligible revisions within the range specified.');
		}

		return $revs;
	}

	/**
	 * Remove revisions not authored by the specified user.
	 * 
	 * @param array $revs   Revisions to filter.
	 * @param mixed $author Username to keep revisions for, or false to not filter.
	 * 
	 * @return array
	 */
	protected function _filterAuthor($revs, $author)
	{
		if (! $author) {
			// No author passed, return all revs.
			return $revs;
		}

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
	 * @param mixed $initial Pass an int to filter out revs prior to this, or false to not filter.
	 * @param mixed $final   Pass an int to filter out revs after this, or false to not filter.
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

	/**
	 * Abstract method that the concrete class must implement.
	 * 
	 * @param array $revs Revisions that have passed all filters.
	 * 
	 * @return void
	 */
	abstract protected function _run($revs);
}