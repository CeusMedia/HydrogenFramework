<?php
/**
 *	...
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
 *	@package		framework.xenon.view.component
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 */
import( 'de.ceus-media.framework.xenon.core.View' );
/**
 *	...
 *	@category		cmClasses
 *	@package		framework.xenon.view.component
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@todo			Code Doc
 */
class Framework_Xenon_View_Component_Navigation extends Framework_Xenon_Core_View
{
	/**
	 *	Builds Navigation View.
	 *	@access		private
	 *	@return		string
	 */
	public function buildNavigation()
	{
		$config		= $this->registry->get( 'config' );
		$request	= $this->registry->get( 'request' );
		$auth		= $this->registry->get( 'auth' );
		$controller	= $this->registry->get( 'controller' );

		$keys	= array( "public" );
		if( $auth->isAuthenticated() )
		{
			$keys[]	= "inside";
			$roles	= Module_Auth_Logic_Role::getUserRoles( FALSE, $auth->getCurrentUser() );
			foreach( $roles as $role )
				$keys[]	= strtolower( str_replace( " ", "_", $role ) );
		}
		else
			$keys[]	= "outside";

		$keys	= array_map( create_function( '$key', 'return "roles/".$key;' ), $keys );
		$keys	= implode( " or ", $keys );

		//  --  LOAD XSL TRANSFORMATION DOCUMENT  --  //
		$xml		= $controller->getDocument();
		$template	= $config['paths.templates']."interface/navigation.xslt";
		$xsl		= file_get_contents( $template );
		$xsl		= str_replace( "{#keys#}", $keys, $xsl );
		$xsl		= DOMDocument::loadXML( $xsl );

		//  --  XSL TRANSFORMATION  --  //
		$processor	= new XSLTProcessor();
		$processor->importStyleSheet( $xsl );
		$result 	= $processor->transformToXML( $xml );

		foreach( $this->words['main']['links_main'] as $linkKey => $linkLabel )
			$result	= str_replace( ">".$linkKey."<", ">__".$linkLabel."__<", $result );
		$result	= str_replace( "__", "", $result );

		$ui	= array(
			'link'		=> $request->get( 'link' ),
			'list'		=> $result,
		);
		$userId	= $auth->getCurrentUser();
		if( $userId )
			$ui['username']	= Module_Auth_Logic_User::getUsername( $userId );
		return $this->loadTemplate( "interface.navigation", $ui );
	}
}
?>