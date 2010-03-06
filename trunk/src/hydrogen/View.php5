<?php
/**
 *	Generic View Class of Framework Hydrogen.
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
 *	@package		framework.hydrogen
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.1
 */
/**
 *	Generic View Class of Framework Hydrogen.
 *	@category		cmClasses
 *	@package		framework.hydrogen
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.1
 */
class Framework_Hydrogen_View
{
	/**	@var		Framework_Hydrogen_Framework	$application	Instance of Framework */
	protected $application;
	/**	@var		array							$data			Collected Data for View */
	protected $data	= array();
	/**	@var		array							$envKeys		Keys of Environment */
	protected $envKeys	= array(
		'dbc',
		'config',
		'session',
		'request',
		'language',
		'messenger',
		'model',
		'controller',
		'action',
		);
	/**	@var		Database_MySQL_Connection		$dbc			Database Connection */
	protected $dbc;
	/**	@var		array							$config			Configuration Settings */
	protected $config;
	/**	@var		Net_HTTP_PartitionSession		$session		Partition Session */
	protected $session;
	/**	@var		Net_HTTP_Request_Receiver		$request		Receiver of Request Parameters */
	protected $request;
	/**	@var		Framework_Hydrogen_Language		$language		Language Support */
	protected $language;
	/**	@var		Framework_Hydrogen_Messenger	$messenger		UI Messenger */
	protected $messenger;
	/**	@var		string							$controller		Name of called Controller */
	protected $controller	= "";
	/**	@var		string							$action			Name of called Action */
	protected $action	= "";

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	public function __construct( Framework_Hydrogen_Environment $env )
	{
		$this->setEnv( $env );
		$this->html	= new UI_HTML_Elements;
		$this->time	= new Alg_Time_Converter();
	}
	
	//  --  SETTERS & GETTERS  --  //
	public function & getData()
	{
		return $this->data;
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		array		$data			Array of Data for View
	 *	@param		string		[$topic]			Topic Name of Data
	 *	@return		void
	 */
	public function setData( $data, $topic = "" )
	{
		if( $topic )
		{
			if( !isset( $this->data[$topic] ) )
				$this->data[$topic]	= array();
			foreach( $data as $key => $value )
				$this->data[$topic][$key]	= $value;
		}
		else
		{
			foreach( $data as $key => $value )
				$this->data[$key]	= $value;
		}
	}

	/**
	 *	Loads Template of View and returns Content.
	 *	@access		public
	 *	@return		string
	 */
	public function loadTemplate()
	{
		$content	= '';
		$filename	= $this->getFilenameOfTemplate( $this->controller, $this->action );
		if( file_exists( $filename ) )
		{
			ob_start();
			extract( $this->data );
			$config		= $this->env->getConfig();
			$request	= $this->env->getRequest();
			$session	= $this->env->getSession();
			$result		= require( $filename );
			$buffer		= ob_get_clean();
			$content	= $result;
			if( $buffer )
			{
				if( !is_string( $content ) )
					$content	= $buffer;
				else
					$this->env->getMessenger()->noteFailure($buffer);
			}
		}
		else
			$this->env->getMessenger()->noteFailure( 'Template "'.$this->controller.'/'.$this->action.'" is not existing' );
		return $content;
	}
	
	/**
	 *	Returns File Name of Template.
	 *	@access		protected
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		string
	 */
	protected function getFilenameOfTemplate( $controller, $action )
	{
		$pathname	= $this->env->getConfig()->get( 'paths.templates' );
		$filename	= $controller."/".$action.".php";
		return $pathname.$filename;
	}
	
	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		Framework_Hydrogen_Environment	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	protected function setEnv( Framework_Hydrogen_Environment $env )
	{
		$this->env			= $env;
		$this->controller	= $this->env->getRequest()->get( 'controller' );
		$this->action		= $this->env->getRequest()->get( 'action' );
	}
}
?>