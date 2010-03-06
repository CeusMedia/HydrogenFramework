<?php
/**
 *	Handler for static Error Pages.
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
 *	@extends		Framework_Xenon_Base
 *	@uses			Framework_Xenon_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			28.03.2008
 *	@version		0.1
 */
import( 'de.ceus-media.framework.xenon.Base' );
/**
 *	Handler for static Error Pages.
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@extends		Framework_Xenon_Base
 *	@uses			Framework_Xenon_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			28.03.2008
 *	@version		0.1
 */
class Framework_Xenon_ErrorPageHandler extends Framework_Xenon_Base
{
	public static $pathPages	= "errors/";

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$configPath			Path to basic Configuration Files
	 *	@param		bool		$identifyLanguage	Flag: use Language-Accept from Browser
	 *	@return		void
	 */
	public function __construct( $configPath = "config/" )
	{
		$type			= array_shift( array_keys( $_REQUEST ) );
		$frameworked	= $this->initApplication( $configPath );
		echo $this->buildView( $type, $frameworked );
	}

	/**
	 *	Tries to initialise an Application Instance and returns Success.
	 *	@access		protected
	 *	@return		bool
	 */
	protected function initApplication( $configPath = "config/" )
	{
		try
		{
			//  --  ENVIRONMENT  --  //
			$this->initRegistry();							//  must be first
			$this->initConfiguration( $configPath );		//  must be one of the first
			$this->initEnvironment();						//  must be one of the first
			$this->initSession();
			$this->initRequest();

			//  --  RESOURCE SUPPORT  --  //
			$this->initDatabase( $configPath );				//  needs Configuration
			$this->initLanguage();							//  needs Request and Session
#			$this->initThemeSupport();						//  needs Configuration, Request and Session

			return TRUE;
		}
		catch( PDOException $e )
		{
		}
		catch( Exception $e )
		{
			throw $e;
		}
		return FALSE;
	}

	/**
	 *	Builds Error Page View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildView( $type, $frameworked )
	{
		if( !$type )
		{
			throw new Exception( "No Error Type defined." );
		}
		if( $frameworked )
		{
			try
			{
				import( 'de.ceus-media.framework.xenon.core.View' );
				$view	= new Framework_Xenon_Core_View();
				$html	= $view->loadContent( self::$pathPages.$type.'.html' );
			}
			catch( Exception $e )
			{
#				die( $e->getMessage() );
				throw new Exception( "Invalid Error Type given." );
			}
		}
		else
		{
			$languages	= array(
				"de",
				"fr",
				"es",
				"it",
			);
			import( 'de.ceus-media.net.http.LanguageSniffer' );
			$sniffer	= new Net_HTTP_LanguageSniffer;
			$language	= $sniffer->getLanguage( $languages );
			$fileName	= "contents/html/".$language."/".self::$pathPages."/".$type.".html";
			$html		= file_get_contents( $fileName );
		}
		return $html;
	}
}
?>