<?php
/**
 *	Generic View Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Generic View Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View
 *	@abstract		Needs to be extended
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class CMF_Hydrogen_View_Helper_Abstract implements CMF_Hydrogen_View_Helper
{
	/**	@var		CMF_Hydrogen_Environment			$env			Environment Object */
	protected		$env								= NULL;

	/**	@var		boolean								$needsEnv		Flag: needs Environment to be set */
	protected		$needsEnv							= TRUE;

	protected function getWords( string $section, string $topic ): array
	{
		$words	= $this->env->getLanguage()->getWords( $topic );
		if( $section && array_key_exists( $section, $words ) )
			return $words[$section];
		return $words;
	}

	/**
	 *	Extendable callback to run after an environment object has been set to this helper.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onSetEnv(){}

	/**
	 *	@todo 		kriss: enable after helper interface is updated
	 */
/*	public function __toString(){
		return $this->render();
	}*/

	/**
	 *	Indicates whether this helper has an environment set.
	 *	@access		public
	 *	@return		boolean
	 *	@todo 		kriss: remove after helper interface is updated
	 */
	public function hasEnv(): bool
	{
		return $this->env instanceof CMF_Hydrogen_Environment;
	}

	/**
	 *	Indicates whether this helper needs to have an environment to be set.
	 *	@access		public
	 *	@return		boolean
	 *	@todo 		kriss: remove after helper interface is updated
	 */
	public function needsEnv()
	{
		return $this->needsEnv;
	}

	/**
	 *	@todo 		kriss: enable after helper interface is updated
	 */
/*	public function render();*/

	/**
	 *	Set environment if needed within this helper.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment Object
	 *	@return		void
	 *	@todo 		kriss: remove after helper interface is updated
	 */
	public function setEnv( CMF_Hydrogen_Environment $env )
	{
		if( $this->needsEnv ){
			$this->env	= $env;
			$this->__onSetEnv();
		}
	}
}
