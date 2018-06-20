<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *
 *	Copyright (c) 2012-2016 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@extends		CMF_Hydrogen_Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			is a web environment needed instead? try to avoid this - maybe a console messenger needs to be implemented therefore
 *	@todo			finish path resolution (path is set twice at the moment)
 */
class CMF_Hydrogen_Environment_Remote extends CMF_Hydrogen_Environment{

	/**	@var	boolean		$hasDatabase		Flag: indicates availability of a database connection */
	public $hasDatabase		= FALSE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		array		$options		Map of environment options
	 *	@return		void
	 */
	public function __construct( $options ){
//		self::$defaultPaths	= CMF_Hydrogen_Environment::$defaultPaths;
		$this->options	= $options;
		$this->path		= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';
		$this->uri		= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';											//

		Loader::registerNew( 'php5', NULL, $this->path.'classes/' );								//  enable autoloader for remote app classes

		self::$configFile	= $this->path."/config/config.ini";

		$this->initClock();																			//  setup clock
		$this->initMessenger();																		//  setup user interface messenger
		$this->initConfiguration();																	//  setup configuration
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initLanguage();

		$this->hasDatabase	= (bool) $this->dbc;													//  note if database is available
		$this->__onInit();
	}

	/**
	 *	Close remote enviroment and keep calling client application alive.
	 *	@access		public
	 *	@param		array		$additionalResources	Not used in remote environment
	 *	@param		boolean		$keepAppAlive			Not used in remote environment
	 *	@return		void
	 */
	public function close( $additionalResources = array(), $keepAppAlive = FALSE){
		parent::close( array(), FALSE );															//  unbind bound resources but keep application alive
	}

	public function getMessenger(){
		return $this->messenger;
	}

	public function initMessenger(){
		$this->messenger	= new CMF_Hydrogen_Environment_Remote_Messenger( $this );
	}
}
class CMF_Hydrogen_Environment_Remote_Messenger extends CMF_Hydrogen_Environment_Resource_Messenger{

	protected function noteMessage( $type, $message ){
		remark( $message );
		flush();
	}
}
?>
