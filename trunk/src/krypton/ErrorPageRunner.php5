<?php
/**
 *	Executes Error Page Handler in Container secured by Exception Handler.
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
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			12.04.2009
 *	@version		0.1
 */
import( 'de.ceus-media.framework.krypton.ErrorPageHandler' );
import( 'de.ceus-media.framework.krypton.ApplicationRunner' );
/**
 *	Executes Error Page Handler in Container secured by Exception Handler.
 *	@category		cmClasses
 *	@package		framework.krypton
 *	@extends		ApplicationRunner
 *	@uses			Framework_Krypton_ErrorPageHandler
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			12.04.2009
 *	@version		0.1
 */
class Framework_Krypton_ErrorPageRunner extends Framework_Krypton_ApplicationRunner
{
	protected function run()
	{
		Framework_Krypton_ErrorPageHandler::loadConstants();
		Framework_Krypton_ErrorPageHandler::$configFile	= CMC_KRYPTON_CONFIG_FILE;
		new Framework_Krypton_ErrorPageHandler();
	}
}
?>