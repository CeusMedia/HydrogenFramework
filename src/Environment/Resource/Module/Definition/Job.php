<?php
/**
 *	Module definition: Job.
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
 *	Module definition: Job.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2024-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Job
{
	/** @var	string				$id */
	public string $id;

	/** @var	string				$class */
	public string $class;

	/** @var	string				$method */
	public string $method;

	/** @var	string				$commands */
	public string $commands;

	/** @var	string				$arguments */
	public string $arguments;

	/** @var	array				$mode */
	public array $mode;

	/** @var	string				$interval */
	public string $interval;

	/** @var	bool				$multiple */
	public bool $multiple;

	/** @var	string				$deprecated */
	public string $deprecated;

	/** @var	string				$disabled */
	public string $disabled;

	/**
	 *	@param		string			$id
	 *	@param		string			$class
	 *	@param		string			$method
	 *	@param		string			$commands
	 *	@param		string			$arguments
	 *	@param		array			$mode
	 *	@param		string			$interval
	 *	@param		bool			$multiple
	 *	@param		string			$deprecated
	 *	@param		string			$disabled
	 */
	public function __construct( string $id, string $class, string $method, string $commands, string $arguments, array $mode, string $interval, bool $multiple, string $deprecated, string $disabled )
	{
		$this->id			= $id;
		$this->class		= $class;
		$this->method		= $method;
		$this->commands		= $commands;
		$this->arguments	= $arguments;
		$this->mode			= $mode;
		$this->interval		= $interval;
		$this->multiple		= $multiple;
		$this->deprecated	= $deprecated;
		$this->disabled		= $disabled;
	}
}
