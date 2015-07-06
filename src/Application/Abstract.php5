<?php
/**
 *	Base application class for Hydrogen application.
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
 *	@package		Hydrogen.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Base application class for Hydrogen application.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Application.Web
 *	@uses			Alg_Object_Factory
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			Code Documentation
 */
abstract class CMF_Hydrogen_Application_Abstract{

	/**	@var		string								$classEnvironment		Class Name of Application Environment to build */
	public static $classEnvironment						= 'CMF_Hydrogen_Environment_Web';
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env					Application Environment Object */
	protected $env;

	public static $modulesNeeded						= array();				//  @todo for PHP 5.3+: make protected and use static:: instead of self:: on use -> now you can set value on App class construction

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
		if( self::$modulesNeeded )																	//  needed modules are defined
			$this->checkNeededModules();															//  check for missing modules
	}

	/**
	 *	Finds missing modules if needed modules are defined.
	 *	Having such, the application will quit with a report.
	 *	@access		protected
	 *	@return		void
	 */
	protected function checkNeededModules(){
		$modulesGot	= array_keys( $this->env->getModules()->getAll() );								//  get installed modules
		$missing	= array_diff( self::$modulesNeeded, $modulesGot );								//  find missing modules
		if( $missing )																				//  there are missing modules
			die( $this->reportMissingModules( $missing ) );											//  quit execution with report
	}

	/**
	 *	Display report of missing modules.
	 *	This method can be customized in applications, see CMF_Hydrogen_Application_Web_Abstract.
	 *	@access		protected
	 *	@param		array		$modules		List of module IDs
	 *	@return		void
	 */
	protected function reportMissingModules( $modules ){
		print( 'Missing modules: '.join( ', ', $modules ) );
	}
	
	abstract public function run();
}
?>
