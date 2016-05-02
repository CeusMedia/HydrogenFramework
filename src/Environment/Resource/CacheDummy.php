<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_CacheDummy{

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always true
	 */
	public function flush(){
		return TRUE;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@param		string		$key			Cache key
	 *	@return		NULL		Always null
	 */
	public function get( $key ){
		return NULL;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always false.
	 */
	public function has( $key ){
		return FALSE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@param		string		$key			Cache key
	 *	@param		mixed		$value			Value to store
	 *	@return		boolean		Always true
	 */
	public function set( $key, $value ){
		return TRUE;
	}

	/**
	 *	Simulate to remove pair from cache.
	 *	@access		public
	 *	@return		boolean		Always true.
	 */
	public function remove( $key ){
		return TRUE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@param		string		$content		Context within cache storage
	 *	@return		void
	 */
	public function setContext( $content ){
	}
}
?>
