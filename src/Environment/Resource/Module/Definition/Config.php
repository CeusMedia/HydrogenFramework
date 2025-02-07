<?php
/**
 *	Module definition: Config.
 *
 *	Copyright (c) 2022-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2022-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition;

/**
 *	Module definition: Config.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2022-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Config
{
	public const string PROTECTED_NO		= 'no';
	public const string PROTECTED_YES		= 'yes';
	public const string PROTECTED_USER		= 'user';

	public string $key;

	/** @var	string|int|float|bool	$value */
	public string|int|float|bool $value;

	/** @var	string|NULL				$type */
	public ?string $type;

	public ?array $values				= NULL;

	/** @var	bool					$mandatory */
	public bool $mandatory				= FALSE;

	public bool|string|NULL $protected	= NULL;

	/** @var	string|NULL				$title */
	public ?string $title;

	/**
	 *	@param		string					$key
	 *	@param		string|int|float|bool	$value
	 *	@param		string|NULL				$type
	 *	@param		string|NULL				$title
	 */
	public function __construct( string $key, string|int|float|bool $value, ?string $type = NULL, ?string $title = NULL )
	{
		$this->key		= $key;
		$this->value	= $value;
		$this->type		= $type;
		$this->title	= $title;
	}
}
