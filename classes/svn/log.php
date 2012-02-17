<?php
/**
 * Subversion log model.
 * 
 * @category Models
 * @package  Subversion
 */

/**
 * Simple Subversion xml log parser.
 */
class Svn_Log
{
	/**
	 * SimpleXML root node.
	 * 
	 * @var object
	 */
	protected $_xml = null;

	/**
	 * Constructor.
	 * 
	 * @param string $xml XML to parse.
	 */
	public function __construct($xml)
	{
		$this->_xml = simplexml_load_string($xml);
	}

	/**
	 * Fetch revisions from the XML as an array of objects.
	 * 
	 * The revisions are keyed on the revision number.
	 * 
	 * @return array
	 */
	public function revisions()
	{
		$revisions = array();

		foreach ($this->_xml->logentry as $entry) {
			$revisions[(int) $entry['revision']] = (object) array(
				'author' => (string) $entry->author,
				'date' => date('Y-m-d H:i:s O', strtotime((string) $entry->date)),
				'msg' => (string) $entry->msg,
			);
		}

		return $revisions;
	}
}