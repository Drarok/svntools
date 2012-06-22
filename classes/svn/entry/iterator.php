<?php
/**
 * Subversion status iterator.
 *
 * @category Utilities
 * @package  Subversion
 */

/**
 * Subversion status iterator class.
 */
class Svn_Entry_Iterator extends ArrayIterator
{
	/**
	 * Cached count of the entries.
	 *
	 * @var integer
	 */
	protected $_count = 0;

	/**
	 * Contructor.
	 *
	 * @param array $entries Array of Svn_Entry instances.
	 */
	public function __construct(array $entries)
	{
		parent::__construct($entries);
		$this->_count = count($entries);
	}

	/**
	 * Return the number of items in the iterator.
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->_count;
	}

	/**
	 * Get a subset of the contents that have the passed state.
	 *
	 * @param string $state State to include in the returned iterator.
	 * @param string ...    Optional further states to include.
	 *
	 * @return Svn_Entry_Iterator
	 */
	public function getEntriesInStates($state)
	{
		$states = func_get_args();
		$entries = array();

		foreach ($this as $entry) {
			if (in_array($entry->getState(), $states)) {
				$entries[] = $entry;
			}
		}

		return new Svn_Entry_Iterator($entries);
	}
}