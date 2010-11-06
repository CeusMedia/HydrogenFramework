<?php
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *
 *	Copyright (c) 2010 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Page extends UI_HTML_PageFrame
{
	protected $packJavaScripts	= FALSE;
	protected $packStyleSheets	= FALSE;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$language	= 'en';
		$this->env	= $env;
		if( $this->env->has( 'language' ) )
			$language	= $this->env->getLanguage()->getLanguage();

		parent::__construct( $language );
		$this->js	= CMF_Hydrogen_View_Helper_JavaScript::getInstance();
		$this->css	= CMF_Hydrogen_View_Helper_StyleSheet::getInstance();
	}

	public function build( $bodyAttributes = array() )
	{
		$this->addHead( $this->css->render( $this->packJavaScripts ) );
		$this->addBody( $this->js->render( $this->packStyleSheets ) );
		return parent::build( $bodyAttributes );
	}

	public function setPackaging( $packJavaScripts = FALSE, $packStyleSheets = FALSE )
	{
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
	}
}
?>
