<?php

class Svn_Entry
{
	const UNVERSIONED = 'unversioned';
	const MODIFIED = 'modified';
	const MISSING = 'missing';
	
	protected $_xml;
	
	protected $_path;
	
	/**
	 * Constructor
	 *
	 * @param SimpleXMLElement $xml  Element parsed from Subversion's XML output.
	 * @param string           $path Relative path to the entry from the wc root.
	 */
	public function __construct(SimpleXMLElement $xml, $path)
	{
		$this->_xml = $xml;
		$this->_path = $path;
	}
	
	public function getPath()
	{
		return $this->_path;
	}
	
	public function getState()
	{
		static $validStates = array(
			self::UNVERSIONED,
			self::MODIFIED,
			self::MISSING,
		);
		
		$state = $this->_xml->{'wc-status'};
		$state = $state['item'];
		
		if (! in_array($state, $validStates)) {
			throw new Exception('Invalid state: ' . $state);
		}
		
		return $state;
	}
}