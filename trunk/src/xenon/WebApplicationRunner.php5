<?php
/**
 *	Executes Web Application in Container secured by Exception Handler.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			29.03.2009
 *	@version		0.1
 */
import( 'de.ceus-media.framework.xenon.WebApplication' );
import( 'de.ceus-media.framework.xenon.ApplicationRunner' );
/**
 *	Executes Web Application in Container secured by Exception Handler.
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@extends		Framework_Xenon_ApplicationRunner
 *	@uses			Framework_Xenon_WebApplication 
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			29.03.2009
 *	@version		0.1
 */
class Framework_Xenon_WebApplicationRunner extends Framework_Xenon_ApplicationRunner
{
	/**
	 *	Runs Web Application, called by Index Script.
	 *	@access		protected
	 *	@return		void
	 */
	protected function run()
	{
		Framework_Xenon_WebApplication::loadConstants();
		Framework_Xenon_WebApplication::$configFile	= CMC_XENON_CONFIG_FILE;
#		if( CMC_XENON_MODE !== CMC_XENON_MODE_PRODUCTION )
#			$this->initErrorHandler();
#		$testError++;												//  Test Error
#		throw new Exception( "This is a Test Exception." );			//  Test Exception
		$application	= new Framework_Xenon_WebApplication();
		$application->act();
		$application->respond();
	}
}
?>