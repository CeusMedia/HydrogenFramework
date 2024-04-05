<?php
/**
 *	Module definition: Hook.
 *
 *	Copyright (c) 2024 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition;

/**
 *	Module definition: Hook.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Hook
{
	/** @var	string		$callback */
	public string $callback;

	/** @var	string		$resource */
	public string $resource;

	/** @var	string		$event */
	public string $event;

	/** @var	int			$level */
	public int $level;

	/**
	 *	@param		string		$callback
	 *	@param		string		$resource
	 *	@param		string		$event
	 *	@param		int			$level
	 */
	public function __construct( string $callback, string $resource, string $event, int $level )
	{
		$this->callback		= $callback;
		$this->resource		= $resource;
		$this->event		= $event;
		$this->level		= $level;
	}
}
