<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CacheDummy
{
	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always true
	 */
	public function flush(): bool
	{
		return TRUE;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@param		string		$key			Cache key
	 *	@return		NULL		Always null
	 */
	public function get( string $key )
	{
		return NULL;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always false.
	 */
	public function has( string $key ): bool
	{
		return FALSE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@param		string		$key			Cache key
	 *	@param		mixed		$value			Value to store
	 *	@return		boolean		Always true
	 */
	public function set( string $key, $value ): bool
	{
		return TRUE;
	}

	/**
	 *	Simulate to remove pair from cache.
	 *	@access		public
	 *	@return		boolean		Always true.
	 */
	public function remove( string $key ): bool
	{
		return TRUE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@param		string		$content		Context within cache storage
	 *	@return		void
	 */
	public function setContext( string $content )
	{
	}
}
