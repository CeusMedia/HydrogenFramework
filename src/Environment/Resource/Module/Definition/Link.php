<?php
/**
 *	Module definition: Link.
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
 *	Module definition: Link.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2024-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Link
{
	public ?string $parent		= NULL;
	public ?string $access		= NULL;
	public ?string $language	= NULL;
	public ?string $path		= NULL;
	public ?string $link		= NULL;
	public ?int $rank			= NULL;
	public ?string $label		= NULL;
	public ?string $icon		= NULL;

	/**
	 *	@param		string|NULL		$parent
	 *	@param		string|NULL		$access
	 *	@param		string|NULL		$language
	 *	@param		string|NULL		$path
	 *	@param		string|NULL		$link
	 *	@param		int|NULL		$rank
	 *	@param		string|NULL		$label
	 *	@param		string|NULL		$icon
	 */
	public function __construct( ?string $parent, ?string $access, ?string $language, ?string $path, ?string $link, ?int $rank, ?string $label, ?string $icon )
	{
		$this->parent		= $parent;
		$this->access		= $access;
		$this->language		= $language;
		$this->path			= $path;
		$this->link			= $link;
		$this->rank			= $rank;
		$this->label		= $label;
		$this->icon			= $icon;
	}
}
