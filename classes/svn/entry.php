<?php
/**
 * Subversion entry model.
 *
 * @category Models
 * @package  Subversion
 */

/**
 * Subversion file entry wrapper class.
 */
class Svn_Entry
{
	/**
	 * Unversioned status constant.
	 *
	 * @const string
	 */
	const UNVERSIONED = 'unversioned';

	/**
	 * Added status constant.
	 *
	 * @const string
	 */
	const ADDED = 'added';

	/**
	 * Modified status constant.
	 *
	 * @const string
	 */
	const MODIFIED = 'modified';

	/**
	 * Conflicted status constant.
	 *
	 * @const string
	 */
	const CONFLICTED = 'conflicted';

	/**
	 * Deleted status constant.
	 *
	 * @const string
	 */
	const DELETED = 'deleted';

	/**
	 * Missing status constant.
	 *
	 * @const string
	 */
	const MISSING = 'missing';

	/**
	 * External status constant.
	 *
	 * @const string
	 */
	const EXTERNAL = 'external';

	/**
	 * Normal status constant.
	 *
	 * @const string
	 */
	const NORMAL = 'normal';

	/**
	 * None status constant.
	 *
	 * @const string
	 */
	const NONE = 'none';

	/**
	 * SimpleXMLElement representing the file entry.
	 *
	 * @var SimpleXMLElement
	 */
	protected $_xml;

	/**
	 * Relative path to the file entry.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Constructor.
	 *
	 * @param SimpleXMLElement $xml  Element parsed from Subversion's output.
	 * @param string           $path Relative path to the entry.
	 */
	public function __construct(SimpleXMLElement $xml, $path)
	{
		$this->_xml = $xml;
		$this->_path = $path;
	}

	/**
	 * Path getter.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Get the file state.
	 *
	 * @return string
	 *
	 * @throws Exception When state is unrecognised.
	 */
	public function getFileState()
	{
		static $validStates = array(
			self::UNVERSIONED,
			self::ADDED,
			self::MODIFIED,
			self::CONFLICTED,
			self::DELETED,
			self::MISSING,
			self::EXTERNAL,
			self::NORMAL,
		);

		$state = $this->_xml->{'wc-status'}['item'];

		if (! in_array($state, $validStates)) {
			throw new Exception('Invalid file state: ' . $state);
		}

		return $state;
	}

	/**
	 * Get the property state.
	 *
	 * @return string
	 *
	 * @throws Exception When state is unrecognised.
	 */
	public function getPropertyState()
	{
		static $validStates = array(
			self::MODIFIED,
			self::CONFLICTED,
			self::NORMAL,
			self::NONE,
		);

		$state = $this->_xml->{'wc-status'}['props'];

		if (! in_array($state, $validStates)) {
			throw new Exception('Invalid property state: ' . $state);
		}

		return $state;
	}
}