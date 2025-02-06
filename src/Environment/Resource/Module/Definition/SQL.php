<?php
/**
 *	Module definition: SQL script for event (install, update, uninstall) at specific version.
 *	Supports different database types.
 *
 *	Copyright (c) 2024-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2024-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition;

/**
 *	Module definition: SQL script for event (install, update, uninstall) at specific version.
 *	Supports different database types.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2024-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class SQL
{
	/** @var	string				$name */
	public string $event;

	/** @var	string				$version */
	public string $version;

	/** @var	string				$type */
	public string $type;

	/** @var	string				$sql */
	public string $sql;

	/**
	 *	@param		string			$event
	 *	@param		string			$version
	 *	@param		string			$type
	 *	@param		string			$sql
	 */
	public function __construct( string $event, string $version, string $type, string $sql )
	{
		$this->event	= $event;
		$this->version	= $version;
		$this->type		= $type;
		$this->sql		= $sql;
	}
}
