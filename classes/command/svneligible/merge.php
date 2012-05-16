<?php

/**
 * Merge the revisions that are eligible.
 * 
 * Merges revisions from a given branch into the working copy.
 */
class Command_Svneligible_Merge extends Command_Svneligible_Filter
{
	/**
	 * Merge all revisions in $revs into the working copy.
	 * 
	 * @param array $revs Filtered revisions to merge.
	 * 
	 * @return void
	 */
	protected function _run($revs)
	{
		if ($this->_getAllEligibleRevisions() == $revs) {
			// If nothing was filtered, then allow Subversion to handle the revisions.
			$this->_svn->merge($this->_options->path);
		} else {
			// Pass the revisions to only merge those specific ones.
			$this->_svn->merge($this->_options->path, $revs);
		}
	}
}