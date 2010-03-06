<?php
/**
 *	Abstract Base Action for Tool Applications.
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
 *	@package		framework.tool
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			27.05.2008
 *	@version		0.1
 */
/**
 *	Abstract Base Action for Tool Applications.
 *	@category		cmClasses
 *	@package		framework.tool
 *	@abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			27.05.2008
 *	@version		0.1
 */
abstract class Framework_Tool_Action
{
	/**	@var		File_Configuration_Reader	$config			Configuration Object */
	protected $config;
	/**	@var		Logic						$logic			Logic Object */
	protected $logic;
	/**	@var		Net_HTTP_Request_Receiver	$request		HTTP Request Object */
	protected $request;
	/**	@var		Net_HTTP_Session			$session		Session Object */
	protected $session;

	/**
	 *	Constructor, calls Actions and stores set Filters.
	 *	@access		public
	 *	@param		Framework_Tool_Environment	$environment	Environment Object
	 *	@return		void
	 */
	public function __construct( Framework_Tool_Environment $environment )
	{
		$this->config		= $environment->getConfiguration();							//  get Configuration Object
		$this->logic		= $environment->getLogic();									//  get Logic Object
		$this->request		= $environment->getRequest();								//  get HTTP Request Object
		$this->session		= $environment->getSession();								//  get Session Object
		$this->handleActions();
	}

	/**
	 *	Handle called Actions. To be overwritten.
	 *	@abstract
	 *	@access		protected
	 *	@return		void
	 */
	abstract protected function handleActions();
}
?>