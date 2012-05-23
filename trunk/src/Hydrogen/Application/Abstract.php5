<?php
/**
 *	Base application class for Hydrogen application.
 *
 *	Copyright (c) 2007-2012 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id: Application.php5 99 2010-09-24 05:48:59Z christian.wuerker $
 */
/**
 *	Base application class for Hydrogen application.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Application.Web
 *	@uses			Alg_Object_Factory
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id: Application.php5 99 2010-09-24 05:48:59Z christian.wuerker $
 *	@todo			Code Documentation
 */
abstract class CMF_Hydrogen_Application_Abstract{

	/**	@var		string								$classEnvironment		Class Name of Application Environment to build */
	public static $classEnvironment						= 'CMF_Hydrogen_Environment_Web';
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env					Application Environment Object */
	protected $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env					Framework Environment
	 *	@return		void
	 */
	public function __construct( $env = NULL )
	{
		if( !$env )
			$env		= Alg_Object_Factory::createObject( self::$classEnvironment );
		else if( is_string( $env ) )
			$env		= Alg_Object_Factory::createObject( $env );
		$this->env		= $env;
	}

	abstract public function run();
}
?>