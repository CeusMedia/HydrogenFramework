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
 *	@package		framework.xenon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			12.04.2009
 *	@version		0.1
 */
import( 'de.ceus-media.framework.xenon.ErrorPageHandler' );
import( 'de.ceus-media.framework.xenon.ApplicationRunner' );
/**
 *	Executes Error Page Handler in Container secured by Exception Handler.
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@extends		ApplicationRunner
 *	@uses			Framework_Xenon_ErrorPageHandler
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			12.04.2009
 *	@version		0.1
 */
class Framework_Xenon_ErrorPageRunner extends Framework_Xenon_ApplicationRunner
{
	protected function run()
	{
		Framework_Xenon_ErrorPageHandler::loadConstants();
		Framework_Xenon_ErrorPageHandler::$configFile	= CMC_XENON_CONFIG_FILE;
		new Framework_Xenon_ErrorPageHandler();
	}
}
?>