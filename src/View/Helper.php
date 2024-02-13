<?php
/**
 *	Interface for view helpers.
 *
 *	Copyright (c) 20010-2024 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View;

use CeusMedia\HydrogenFramework\Environment;

/**
 *	Interface for view helpers.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			rename to HelperInterface, update view helper classes in all modules
 */
interface Helper
{
	public function hasEnv(): bool;

	public function needsEnv(): bool;

	/**
	 *	@param		Environment		$env
	 *	@return		self
	 */
	public function setEnv( Environment $env ): self;

//	@todo 	see if this pattern (having render method) is realizable for all existing helpers
//	public function render();
//	public function __toString();

//	@todo 	whats with __construct( $env ) for all helpers?
//	public function __construct( Environment $env )
//	{
//		$this->env	= $env;
//	}

//	@todo 	mind the idea of a "helper pool", like the logic pool

}
