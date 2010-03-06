<?php
/**
 *	Controller for Pages requested by Links.
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
 *	@uses			Framework_Krypton_Core_Registry
 *	@uses			Framework_Krypton_Core_PageDefinitionReader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.6
 */
import( 'de.ceus-media.framework.krypton.core.Registry' );
/**
 *	Controller for Pages requested by Links.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@uses			Framework_Krypton_Core_Registry
 *	@uses			Framework_Krypton_Core_PageDefinitionReader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.6
 */
class Framework_Krypton_Core_PageController
{
	/**	@var		array		$default		Default Page */
	private $default			= null;
	/** @var		DOMDocument	$document		Pages XML as DOM Document */
	private $document			= null;
	/**	@var		string		$fileName		File Name of Pages XML */
	public	$fileName;
	/**	@var		array		$pages			Array of Pages from Page XML File */
	protected $pages			= array();
	/**	@var		array		$cachedScopes	Cached Page to Scope Relations */
	private $cachedScopes		= array();
	/**	@var		string		$cacheFile		File Name of Pages Cache File */	
	private $cacheFile			= null;
	

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$fileName		File Name of Page XML File
	 *	@return		void
	 */
	public function __construct( $fileName )
	{
		$config		= Framework_Krypton_Core_Registry::getStatic( 'config' );

		if( !file_exists( $fileName ) )
			throw new RuntimeException( 'Missing '.$fileName );
		$this->fileName		= $fileName;
		$this->cacheFile	= $config['paths.cache'].basename( $fileName ).".cache";
		$this->readPages();
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
		import( 'de.ceus-media.framework.krypton.core.PageDefinitionEditor' );
		$editor	= new Framework_Krypton_Core_PageDefinitionEditor( $this->fileName );
		if( $editor->addRoleToPage( $role, $pageId ) )
		{
			$this->clearCache();
			$this->readPages();
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *	Indicates whether a Page is existing for a Link.
	 *	@access		public
	 *	@param		string		$pageId			Page ID to check
	 *	@param		string		$scope			Scope of Page Set
	 *	@return		bool
	 */
	public function checkPage( $pageId, $scope = "" )
	{
		if( $scope )
		{
			if( !isset( $this->pages[$scope] ) )
				return FALSE;
			if( !array_key_exists( strtolower( $pageId ), $this->pages[$scope] ) )
				return FALSE;
			$this->cachedScopes[$pageId] = $scope;
			return TRUE;
		}
		
		foreach( array_keys( $this->pages ) as $scope )
			if( $this->checkPage( $pageId, $scope ) )
				return TRUE;
		return FALSE;
	}

	/**
	 *	Removes Cache File of Pages.
	 *	@access		public
	 *	@param		bool		$clearScopes	Flag: clear Scope Map
	 *	@param		bool		$clearCacheFile	Flag: remove Pages Cache File
	 *	@return		void
	 */
	public function clearCache( $clearScopes = true, $clearCacheFile = true )
	{
		if( $clearScopes )
			$this->cachedScopes	= array();
			
		if( $clearCacheFile && file_exists( $this->cacheFile ) )
			unlink( $this->cacheFile );
	}

	/**
	 *	Creates nested Folder recursive.
	 *	@access		protected
	 *	@param		string		$path			Folder to create
	 *	@return		void
	 */
	protected function createFolder( $path )
	{
		if( !file_exists( $path ) )
		{
			$parts	= explode( "/", $path );
			$folder	= array_pop( $parts );
			$path	= implode( "/", $parts );
			$this->createFolder( $path );
			mkDir( $path."/".$folder );
		}
	}

	/**
	 *	Returns (factorised) Class Name of Link.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@param		string		$prefix			Class Prefix (view,action,...)
	 *	@return		string
	 */
	public function getClassName( $pageId, $prefix = "", $category = "" )
	{
		if( !$this->checkPage( $pageId ) )
			throw new InvalidArgumentException( 'Page "'.$pageId.'" is not defined.' );

		$registry	= Framework_Krypton_Core_Registry::getInstance();
		$pageId		= strtolower( $pageId );
		$scope		= $this->getPageScope( $pageId );
		$page		= $this->pages[$scope][$pageId];
		if( isset( $page['factory'] ) && $page['factory'] )
		{
			$factory	= $page['factory'];
			try
			{
				$factory	= $registry->get( $factory );
			}
			catch( Exception $e )
			{
				throwException( 'Logic', 'No Category Factory "'.$factory.'" available.' );
			}
			return $factory->getClassName( $page['file'], $prefix, $category );
		}
		if( $prefix )
			$prefix	= ucFirst( $prefix )."_";
		return $prefix.$page['file'];
	}

	/**
	 *	Collects and returns all default Pages.
	 *	@access		public
	 *	@return		array
	 */
	public function getDefaultPages()
	{
		$list	= array();
		foreach( $this->pages as $scope => $pages )
		{
			foreach( $pages as $pageId => $page )
			{
				$isDefault	= isset( $page['default'] ) && $page['default'];
				$isHidden	= isset( $page['hidden'] ) && $page['hidden'];
				if( $isDefault && !$isHidden )
					$list[]	= $pageId;
			}
		}
		return $list;
	}

	/**
	 *	Returns Page XML as DOM Document.
	 *	@access		public
	 *	@return		void
	 */
	public function getDocument()
	{
		if( !$this->document )
		{
			$this->document	= new DOMDocument();
			$this->document->preserveWhiteSpace	= true;
			$this->document->validateOnParse = true;
			$this->document->load( $this->fileName );
		}
		return $this->document;
	}

	/**
	 *	Returns allowed Roles for Page.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@return		array
	 */
	public function getPageRoles( $pageId )
	{
		$scope	= $this->getPageScope( $pageId );
		if( !$this->checkPage( $pageId, $scope ) )
			throw new InvalidArgumentException( 'Page "'.$pageId.'" is not defined in Scope "'.$scope.'".' );
		return $this->pages[$scope][$pageId]['roles'];
	}

	/**
	 *	Returns Array of Pages from Page XML Document.
	 *	@access		public
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
	 *	Returns Scope of Page.
	 *	@access		public
	 *	@param		string		Page ID
	 *	@return		string
	 */
	public function getPageScope( $pageId )
	{
		if( isset( $this->cachedScopes[$pageId] ) )
			return $this->cachedScopes[$pageId];
		foreach( $this->pages as $scope => $page )
			if( $this->checkPage( $pageId, $scope ) )
				return $this->cachedScopes[$pageId] = $scope;
		throw new InvalidArgumentException( 'Page "'.$pageId.'" is not defined.' );
	}

	/**
	 *	Returns Source File of Page.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@return		string
	 */
	public function getSource( $pageId )
	{
		if( !$this->checkPage( $pageId ) )
			throw new InvalidArgumentException( 'Page "'.$pageId.'" is not defined.' );
			
		$scope	= $this->getPageScope( $pageId );
		$pageId	= strtolower( $pageId );
		$page	= $this->pages[$scope][$pageId];
		return $page['file'];
	}
	
	/**
	 *	Indicates whether a Page is a disabled.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@return		bool
	 */
	public function isDisabled( $pageId )
	{
		if( !$this->checkPage( $pageId ) )
			return FALSE;
		$scope	= $this->getPageScope( $pageId );
		$page	= $this->pages[$scope][$pageId];
		return isset( $page['disabled'] ) && $page['disabled'];
	}

	/**
	 *	Indicates whether a Page is a dynamic Page.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@param		string		$scope			Scope of Page
	 *	@return		bool
	 */
	public function isDynamic( $pageId )
	{
		if( $this->checkPage( $pageId ) )
		{
			$pageId	= strtolower( $pageId );
			$scope	= $this->getPageScope( $pageId );
			$page	= $this->pages[$scope][$pageId];
			return isset( $page['type'] ) && $page['type'] == "dynamic";
		}
		return FALSE;
	}
	
	/**
	 *	Indicates whether a Page is a hidden.
	 *	@access		public
	 *	@param		string		$pageId			Page ID
	 *	@return		bool
	 */
	public function isHidden( $pageId )
	{
		if( !$this->checkPage( $pageId ) )
			return FALSE;
		$scope	= $this->getPageScope( $pageId );
		$page	= $this->pages[$scope][$pageId];
		return isset( $page['hidden'] ) && $page['hidden'];
	}

	/**
	 *	Reads Page XML File and write Cache File.
	 *	@access		private
	 *	@return		void
	 */
	protected function readPages()
	{
		if( file_exists( $this->cacheFile ) && filemtime( $this->cacheFile ) >= filemtime( $this->fileName ) )
		{
			$this->pages	= unserialize( file_get_contents( $this->cacheFile ) );
		}
		else
		{
			import( 'de.ceus-media.framework.krypton.core.PageDefinitionReader' );
			$reader			= new Framework_Krypton_Core_PageDefinitionReader( $this->getDocument() );
			$this->pages	= $reader->getPages();
			$this->createFolder( dirname( $this->cacheFile ) );
			file_put_contents( $this->cacheFile, serialize( $this->pages ) );
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
		import( 'de.ceus-media.framework.krypton.core.PageDefinitionEditor' );
		$editor	= new Framework_Krypton_Core_PageDefinitionEditor( $this->fileName );
		if( $editor->removeRoleFromPage( $role, $pageId ) )
		{
			$this->clearCache();
			$this->readPages();
			return TRUE;
		}
		return FALSE;
	}
}
?>