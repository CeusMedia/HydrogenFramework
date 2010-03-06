<?php
/**
 *	Editor for XML Page Definitions.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@uses			Framework_Krypton_Exception_IO
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.6
 */
import( 'de.ceus-media.framework.krypton.exception.IO' );
/**
 *	Editor for XML Page Definitions.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@uses			Framework_Krypton_Exception_IO
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.6
 */
class Framework_Krypton_Core_PageDefinitionEditor
{
	/**	@var		string		$fileName		File Name of Page Definition XML File */
	protected $fileName;
	/**	@var		DOMDocument	$document		DOM Document of Page Definition XML File */
	protected $document;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$fileName		File Name of Page Definition XML File
	 *	@return		void
	 */
	public function __construct( $fileName )
	{
		$this->fileName	= $fileName;
	}

	/**
	 *	Adds a Role to a Page.
	 *	@access		public
	 *	@param		string		$role			Role Name
	 *	@param		string		$pageId			Page ID
	 *	@return		bool
	 */
	public function addRoleToPage( $role, $pageId )
	{
		$this->loadDocument();
		$pageNode	= $this->document->getElementById( $pageId );
		if( !$pageNode )
			throw new Framework_Krypton_Exception_IO( 'page_not_existing' );
		$accessNode	= $pageNode->getElementsByTagName( 'roles' )->item( 0 );
		$newNode	= $this->document->createElement( $role );
		$accessNode->appendChild( $newNode );
		return $this->saveDocument();
	}

	/**
	 *	Sets 'disabled' Attribute of a Page Node in Page Definition File.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@return		bool
	 */
	public function disablePage( $pageId )
	{
		return $this->setPageAttribute( $pageId, 'disabled', 1 );
	}

	/**
	 *	Removes 'disabled' Attribute of a Page Node in Page Definition File.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@return		bool
	 */
	public function enablePage( $pageId )
	{
		return $this->setPageAttribute( $pageId, 'disabled', 0 );
	}

	/**
	 *	Sets 'hidden' Attribute of a Page Node in Page Definition File.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 */
	public function hidePage( $pageId )
	{
		return $this->setPageAttribute( $pageId, 'hidden', 1 );
	}

	/**
	 *	Loads XML Page Definitions.
	 *	@access		private
	 *	@return		void
	 */
	private function loadDocument()
	{
		if( !$this->document )
		{
			$this->document	= new DOMDocument();
			$this->document->preserveWhiteSpace	= true;
			$this->document->validateOnParse	= true;
			$this->document->formatOutput		= true;
			$this->document->load( $this->fileName );
		}
	}

	/**
	 *	Removes a Role from a Page.
	 *	@access		public
	 *	@param		string		$role			Role Name
	 *	@param		string		$pageId			Page ID
	 *	@return		bool
	 */
	public function removeRoleFromPage( $role, $pageId )
	{
		$this->loadDocument();
		$pageNode	= $this->document->getElementById( $pageId );
		if( !$pageNode )
			throw new Framework_Krypton_Exception_IO( 'page_not_existing' );
		$accessNode	= $pageNode->getElementsByTagName( 'roles' )->item( 0 );
		$oldNode	= $accessNode->getElementsByTagName( $role )->item( 0 );
		if( !$oldNode )
			throw new Framework_Krypton_Exception_IO( 'page_role_not_existing' );
		$accessNode->removeChild( $oldNode );
		return $this->saveDocument();
	}

	/**
	 *	Saves XML Page Definitions.
	 *	@access		private
	 *	@return		bool
	 */
	private function saveDocument()
	{
		$result	= $this->document->save( $this->fileName );
		return $result !== FALSE;
	}

	/**
	 *	Sets Attribute of Page Node in Page Definition File.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@param		string		$key			Attribute Key
	 *	@param		string		$value			Attribute Value
	 */
	public function setPageAttribute( $pageId, $key, $value )
	{
		$this->loadDocument();
		$pageNode	= $this->document->getElementById( $pageId );
		if( !$pageNode )
			throw new Framework_Krypton_Exception_IO( 'page_not_existing' );
		$pageNode->setAttribute( $key, $value );
		return $this->saveDocument();

	}

	/**
	 *	Removes 'hidden' Attribute of a Page Node in Page Definition File.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 */
	public function showPage( $pageId )
	{
		return $this->setPageAttribute( $pageId, 'hidden', 0 );
	}
}
?>