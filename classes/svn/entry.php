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
	 * Modified status constant.
	 *
	 * @const string
	 */
	const MODIFIED = 'modified';

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
	 * State getter.
	 *
	 * @return string
	 *
	 * @throws Exception When state is unknown.
	 */
	public function getState()
	{
		static $validStates = array(
			self::UNVERSIONED,
			self::MODIFIED,
			self::MISSING,
			self::EXTERNAL,
		);

		$state = $this->_xml->{'wc-status'};
		$state = $state['item'];

		if (! in_array($state, $validStates)) {
			throw new Exception('Invalid state: ' . $state);
		}

		return $state;
	}
}