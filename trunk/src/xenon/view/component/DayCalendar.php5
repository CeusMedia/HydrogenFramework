<?php
/**
 *	Builds HTML for Day Calendar.
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
 *	@extends		Framework_Xenon_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			17.03.2007
 *	@version		0.1
 */
/**
 *	Builds HTML for Day Calendar.
 *	@category		cmClasses
 *	@package		framework.xenon.view.component
 *	@extends		Framework_Xenon_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			17.03.2007
 *	@version		0.1
 */
class Framework_Xenon_View_Component_DayCalendar extends Framework_Xenon_Core_View
{
	/**	@var		string		$format			Format for Input Field */
	var $format		= "%m-%d-%Y %H:%M";
	/**	@var		bool		$type			Type of Calendar (-1:past|1:future) */
	var $type		= 1;
	/**	@var		int			$range			Range of Years */
	var $range		= 75;
	
	/**
	 *	Builds Calendar with Opener and Calendar Layer.
	 *	@access		public
	 *	@param		string		$id_input	ID of Input Field
	 *	@param		string		$id_opener	ID of Opener
	 *	@return		string
	 */
	public function buildCalendar( $id_input, $id_opener )
	{
		$ui['format']		= $this->format;
		$ui['id_input']		= $id_input;
		$ui['id_opener']	= $id_opener;

		$ui['range_start']	= (int)date( "Y" ) - 2;
		$ui['range_stop']	= (int)date( "Y" ) + 2;

		if( $this->range && $this->type != 0 )
		{
			if( $this->type	== -1 )
			{
				$ui['range_start']	= (int)date( "Y" ) - $this->range;
				$ui['range_stop']	= date( "Y" );
			}
			if( $this->type	== 1 )
			{
				$ui['range_start']	= date( "Y" );
				$ui['range_stop']	= (int)date( "Y" ) + $this->range;
			}
		}
		return $this->loadTemplate( 'daycalendar', $ui );
	}
	
	/**
	 *	Sets Format for Input Field.
	 *	@access		public
	 *	@param		string		$format		Format for Input Field (eg. y/m)
	 *	@return		void
	 */
	public function setFormat( $format )
	{
		$this->format	= $format;
	}

	/**
	 *	Sets Range of Years.
	 *	@access		public
	 *	@param		int			$years		Range of Years
	 *	@return		void
	 */
	public function setRange( $years )
	{
		$this->range	= abs( $years );
	}
	
	/**
	 *	Sets Type to 'future' or 'past'.
	 *	@access		public
	 *	@param		int		$type		Type of Calendar (-1:past|1:future)
	 *	@return		void
	 */
	public function setType( $type )
	{
		$this->type	= $type;
	}
}
?>
