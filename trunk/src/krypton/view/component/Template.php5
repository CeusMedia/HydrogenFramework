<?php
/**
 *	Template Component.
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
 *	@package		framework.krypton.view.component
 *	@extends		UI_Template
 *	@uses			Framework_Krypton_Core_Registry
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			02.03.2007
 *	@version		0.2
 */
import( 'de.ceus-media.ui.Template' );
/**
 *	Template Component.
 *	@category		cmClasses
 *	@package		framework.krypton.view.component
 *	@extends		UI_Template
 *	@uses			Framework_Krypton_Core_Registry
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			02.03.2007
 *	@version		0.2
 */
class Framework_Krypton_View_Component_Template extends UI_Template
{
	/**
	 *	Returns Template File URI.
	 *	@access		public
	 *	@param		string		$fileKey		File Name of Template File
	 *	@return		string
	 */
	protected function getTemplateUri( $fileKey )
	{
		$config		= Framework_Krypton_Core_Registry::getStatic( 'config' );
		$basePath	= $config['paths.templates'];
		$baseName	= str_replace( ".", "/", $fileKey ).".html";
		$fileName	= $basePath.$baseName;
		return $fileName;
	}

	/**
	 *	Loads a new template file if it exists. Otherwise it will throw an Exception.
	 *	@access		public
	 *	@param		string		$fileName		File Name of Template
	 *	@return		void
	 */
	public function setTemplate( $fileName )
	{
		$filePath	= $this->getTemplateUri( $fileName );
		if( !file_exists( $filePath ) )									//  check file
			throw new Exception_Template( EXCEPTION_TEMPLATE_FILE_NOT_FOUND, $fileName, $filePath );

		$this->fileName	= $fileName;
		$this->template = file_get_contents( $filePath );
	}
}  
?>