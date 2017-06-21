<?php
/**
 *	Interface of Hydrogen environment.
 *
 *	Copyright (c) 2007-2016 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Interface of Hydrogen environment.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
interface CMF_Hydrogen_Environment
{
	const MODE_UNKNOWN	= 0;
	const MODE_DEV		= 1;
	const MODE_TEST		= 2;
	const MODE_STAGE	= 4;
	const MODE_LIVE		= 8;
}
?>
