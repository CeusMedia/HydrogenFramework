<?php
/**
 *	Application class for a console program.
 *
 *	Copyright (c) 2014-2021 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Application class for a console program.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class CMF_Hydrogen_Application_Console extends CMF_Hydrogen_Application_Abstract
{
	public function __construct( CMF_Hydrogen_Environment $env = NULL )
	{
		if( self::$classEnvironment === 'CMF_Hydrogen_Environment_Web' )
			self::$classEnvironment	= 'CMF_Hydrogen_Environment_Console';
		parent::__construct( $env );
//		$this->env->set( 'request', new Console_Command_ArgumentParser() );
	}

	/**
	 *	General main application method.
	 *	You can copy and modify this method in your application to handle exceptions your way.
	 *	NOTE: You need to execute $this->respond( $this->main() ) in order to start dispatching, controlling and rendering.
	 *	@access		public
	 *	@return		void
	 */
	public function run()
	{
		throw new RuntimeException( 'Not implemented' );
/*		error_reporting( E_ALL );
		try{
			$this->dispatch();
			if( $this->messenger->count() )
				die( "MSG!!!");
			$this->env->close();																	//  teardown environment and quit application execution
		}
		catch( Exception $e ){
			die( "Error: ".$e->getMessage()."\n" );
		}*/
	}

	//  --  PROTECTED  --  //

	/**
	 *	Executes called command.
	 *	@access		protected
	 *	@todo		implement
	 *	@throws		RuntimeException	since not implemented yet
	 */
	protected function dispatch( $default = NULL )
	{
		throw new RuntimeException( 'Dispatching is disabled for console applications' );
	}
}
