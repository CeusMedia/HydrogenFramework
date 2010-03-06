<?php
/**
 *	Main Class of Motrada V2 to realize Actions and build Views.
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
 *	@package		framework.krypton
 *	@extends		Framework_Krypton_Base
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.12.2006
 *	@version		0.3
 */
import( 'de.ceus-media.framework.krypton.Base' );
/**
 *	Main Class of Motrada V2 to realize Actions and build Views.
 *	@category		cmClasses
 *	@package		framework.krypton
 *	@extends		Framework_Krypton_Base
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.12.2006
 *	@version		0.3
 */
abstract class Framework_Krypton_ConsoleApplicationAbstract extends Framework_Krypton_Base
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		bool		$verbose		Flag: print Information to Console
	 *	@return		void
	 */
	abstract public function __construct( $verbose = TRUE );

	/**
	 *	Evaluates Arguments.
	 *	@access		protected
	 *	@return		void
	 */
	abstract protected function evaluateArguments();

	/**
	 *	Logs Exception Information into Log File.
	 *	@access		protected
	 *	@param		Exception	$e				Exception to log
	 *	@param		string		$className		Name of Job Class
	 *	@param		bool		$verbose		Flag: print Information to Console
	 *	@return		void
	 */
	abstract protected function logException( Exception $e, $className, $verbose = FALSE );

	/**
	 *	Runs Job Class as Console Application.
	 *	@access		protected
	 *	@param		bool		$verbose		Flag: print Information to Console
	 *	@return		void
	 */
	abstract protected function run( $verbose = TRUE );
	
	abstract protected function showHelp();
}
?>