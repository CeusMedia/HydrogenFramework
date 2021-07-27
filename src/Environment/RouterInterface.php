<?php
/**
 *	Router interface.
 *
 *	Copyright (c) 2011-2021 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\HydrogenFramework\Environment;

/**
 *	Router interface.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
interface RouterInterface
{
	public function __construct( Environment $env );

	public function getAbsoluteUri( string $controller = NULL, string $action = NULL, array $arguments = array(), array $parameters = array(), string $fragmentId = NULL ): string;

	public function getRelativeUri( string $controller = NULL, string $action = NULL, array $arguments = array(), array $parameters = array(), string $fragmentId = NULL ): string;

	public function parseFromRequest();
}
