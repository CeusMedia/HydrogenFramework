<?php
/**
 *	Generic View Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2012 Christian Würker (ceusmedia.com)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Generic View Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View
 *	@abstract		Needs to be extended
 *	@implements		CMF_Hydrogen_View_Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
abstract class CMF_Hydrogen_View_Helper_Abstract implements CMF_Hydrogen_View_Helper
{
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Environment Object */
	protected		$env								= NULL;
	/**	@var		boolean								$needsEnv		Flag: needs Environment to be set */
	protected		$needsEnv							= TRUE;

	/**
	 *	Extendable callback to run after an environment object has been set to this helper.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onSetEnv(){}

	/**
	 *	Indicates whether this helper has an environment set.
	 *	@access		public
	 *	@return		boolean
	 */
	public function hasEnv()
	{
		return $this->env instanceof CMF_Hydrogen_Environment;
	}

	/**
	 *	Indicates whether this helper needs to have an environment to be set.
	 *	@access		public
	 *	@return		boolean
	 */
	public function needsEnv()
	{
		return $this->needsEnv;
	}

	/**
	 *	Set environment if needed within this helper.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment Object
	 *	@return		void
	 */
	public function setEnv( CMF_Hydrogen_Environment $env )
	{
		if( $this->needsEnv )
		{
			$this->env	= $env;
			$this->__onSetEnv();
		}
	}
}
?>
