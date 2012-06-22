<?php

/**
 * Merge the revisions that are eligible.
 * 
 * Merges revisions from a given branch into the working copy.
 */
class Command_Svneligible_Merge extends Command_Svneligible_Filter
{
	protected function _setUp()
	{
		// Only merge if the working copy is clean.
		if ($this->_isWorkingCopyDirty()) {
			throw new Exception('Refusing to merge due to uncommitted changes.');
		}
	}

	/**
	 * Merge all revisions in $revs into the working copy.
	 * 
	 * @param array $revs Filtered revisions to merge.
	 * 
	 * @return void
	 */
	protected function _run($revs)
	{
		// Detect if the user specified a record-only merge.
		$recordOnly = $this->_args->getNamedArgument('record-only', false);

		if ($this->_getAllEligibleRevisions() == $revs) {
			// If nothing was filtered, then allow Subversion to handle the revisions.
			$this->_svn->merge($this->_options->path, null, null, false, $recordOnly);
		} else {
			// Pass the revisions to only merge those specific ones.
			$this->_svn->merge($this->_options->path, $revs, null, false, $recordOnly);
		}
	}
}