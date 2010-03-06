<?php
/**
 *	Generic Definition Action Handler.
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
 *	@package		framework.xenon.core
 *	@extends		Framework_Xenon_Core_Action
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			18.06.2006
 *	@version		0.6
 */
import( 'de.ceus-media.framework.xenon.core.Action' );
/**
 *	Generic Definition Action Handler.
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@extends		Framework_Xenon_Core_Action
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			18.06.2006
 *	@version		0.6
 */
class Framework_Xenon_Core_DefinitionAction extends Framework_Xenon_Core_Action
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $useWikiParser = FALSE )
	{
		parent::__construct( $useWikiParser );
		$this->loadBaseLanguageFile( 'validator', FALSE, FALSE );
	}
}
?>
