<?php
/**
 *	Reads Pagesets and Pages from DOM Document.
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
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.6
 */
/**
 *	Reads Pagesets and Pages from DOM Document.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.6
 */
class Framework_Krypton_Core_PageDefinitionReader
{
	/**	@var		array		$pages			Array of Pages from Page XML File */
	private $pages	= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DOMDocument	$document		Pages XML as DOM Document
	 *	@return		void
	 */
	public function __construct( $document )
	{
		$this->readPageSets( $document );
	}

	/**
	 *	Returns Array of Pages from Page XML Document.
	 *	@access		public
	 *	@param		string		$scope		Scope of Pageset
	 *	@return		array
	 */
	public function getPages( $scope = "" )
	{
		if( !$scope )
			return $this->pages;
		if( !isset( $this->pages[$scope] ) )
			throw new InvalidArgumentException( 'Scope "'.$scope.'" is not defined.' );
		return $this->pages[$scope];
	}

	/**
	 *	Reads a single Page Entry from DOM Node.
	 *	@access		private
	 *	@param		DOMNode		$page		DOM Node of Page
	 *	@param		string		$scope		Scope of Pageset
	 *	@param		string		$parentId	ID of parent Page
	 *	@return		void
	 */
	private function readPage( $page, $scope, $parentId = false )
	{
		$data	= array(
			'scope'		=> strtolower( $scope ),
			'id'		=> strtolower( $page->getAttribute( 'id' ) ),
			'default'	=> (bool) $page->getAttribute( 'default' ),
			'hidden'	=> (bool) $page->getAttribute( 'hidden' ),
			'disabled'	=> (bool) $page->getAttribute( 'disabled' ),
			'cache'		=> (bool) $page->getAttribute( 'cache' ),
		);
		foreach( $page->childNodes as $node )
		{
			$tag	= $node->nodeName;
			if( $tag == "subpages" )
			{
				foreach( $node->childNodes as $subpage )
					if( $subpage->nodeType == XML_ELEMENT_NODE )
						$this->readPage( $subpage, $scope, $data['id'] );
				continue;
			}
			else if( $tag == "roles" )
			{
				$data['roles']	= array();
				foreach( $node->childNodes as $role )
					if( $role->nodeType == XML_ELEMENT_NODE )
						$data['roles'][]	= $role->nodeName;
			}
			else if( $tag == "source" )
			{
				$data['type']		= $node->getAttribute( 'type' );
				$data['file']		= $node->getAttribute( 'file' );
				$data['factory']	= $node->getAttribute( 'factory' );
			}
		}
		if( $parentId )
			$data['parent']	= $parentId;
		$this->pages[$scope][$data['id']]	= $data;
	}
	
	/**
	 *	Reads Pagesets from Page Definitions.
	 *	@access		private
	 *	@param		DOMDocument	$document		Pages XML as DOM Document
	 *	@return		void
	 */
	private function readPageSets( $document )
	{
		$pagesets	= $document->documentElement->childNodes;
		foreach( $pagesets as $pageset )
		{
			if( !count( $pageset->childNodes ) )
				continue;
			$scope	= $pageset->getAttribute( 'scope' );
			$pages	= $pageset->childNodes;
			foreach( $pages as $page )
			{
				if( !count( $page->childNodes ) )
					continue;
				$this->readPage( $page, $scope );
			}
		}
	}
}
?>