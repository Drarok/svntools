<?php

/**
 * This is where common code for the 'merge' and 'show' commands lives.
 */
abstract class Command_Svneligible_Filter extends Command_Svneligible
{
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