<?php /** @noinspection PhpUnused */

/**
 *	...
 *
 *	Copyright (c) 2010-2024 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\HydrogenFramework\View\Helper\StyleSheet as CssHelper;
use DomainException;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class StyleCascade
{
	/** @todo remove this legacy after modules have been updated */
	public CssHelper $common;
	public CssHelper $lib;
	public CssHelper $primer;
	public CssHelper $theme;

	/**	@var	array<CssHelper>		$levels */
	protected array $levels				= [];

	/**
	 *	Constructor.
	 *	@param		array<string,string|NULL>	$levels			Map of level keys and related paths, holding CSS files
	 */
	public function __construct( array $levels = [] )
	{
		foreach( $levels as $key => $basePath )
			$this->levels[$key]	= new CssHelper( $basePath );

		/** @todo remove this legacy after modules have been updated */
		$this->primer	= $this->get( 'primer' );
		$this->common	= $this->get( 'common' );
		$this->theme	= $this->get( 'theme' );
		$this->lib		= $this->get( 'lib' );
	}

	/**
	 *	@param		string $key
	 *	@return		CssHelper
	 */
	public function get( string $key ): CssHelper
	{
		if( array_key_exists( $key, $this->levels ) )
			return $this->levels[$key];
		throw new DomainException( 'Invalid CSS cascade level key' );
	}
}