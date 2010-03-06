<?php
/**
 *	Builds HTML for Month Calendar.
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
 *	@extends		Framework_Krypton_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.03.2007
 *	@version		0.1
 */
/**
 *	Builds HTML for Month Calendar.
 *	@category		cmClasses
 *	@package		framework.krypton.view.component
 *	@extends		Framework_Krypton_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.03.2007
 *	@version		0.1
 */
class Framework_Krypton_View_Component_MonthCalendar extends Framework_Krypton_Core_View
{
	const TYPE_PAST 		= 0;
	const TYPE_PRESENT		= 1;
	const TYPE_FUTURE		= 2;
	
	/**	@var	array		$months			Array of Months */
	var $months	= array(
		'01'	=> "Januar",
		'02'	=> "Februar",
		'03'	=> "März",
		'04'	=> "April",
		'05'	=> "Mail",
		'06'	=> "Juni",
		'07'	=> "Juli",
		'08'	=> "August",
		'09'	=> "September",
		'10'	=> "Oktober",
		'11'	=> "November",
		'12'	=> "Dezember",
	);
	
	/**	@var	string		$format			Format for Input Field */
	var $format	= "m.y";
	/**	@var	int			$year			Year to start from */
	var $year;
	/**	@var	int			$type			Type of Calendar (TYPE_PAST|TYPE_PRESENT|TYPE_FUTURE) */
	var $type	= self::TYPE_PRESENT;
	/**	@var	bool		$asc			Direction where true is ascending */
	var $asc	= true;
	/**	@var	int			$range			Range of Years */
	var $range	= 75;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->html	= new UI_HTML_Elements;
		$this->year	= date( "Y" );
	}
	
	/**
	 *	Builds Calendar with Opener and Calendar Layer.
	 *	@access		public
	 *	@param		string		$id_input	ID of Input Field
	 *	@param		string		$id_mcal	ID of Calendar Layer
	 *	@param		string		$id_opener	ID of Opener
	 *	@param		string		$js_name	Name of JavaScript Instance of UI.MonthCalendar
	 *	@return		string
	 */
	public function buildCalendar( $id_input, $id_mcal, $id_opener, $js_name )
	{
		$opt_year	= $this->buildYears();
		$ui['select_year']	= $this->html->Select( 'year', $opt_year, 's' );
		foreach( $this->months as $key => $value )
		{
			$link	= $this->html->Link( "javascript:".$js_name.".select('".$key."');", $value );
			$list[]	= $this->html->ListItem( $link );
		}
		$ui['list']			= $this->html->unorderedList( $list );
		$ui['format']		= $this->format;
		$ui['id_input']		= $id_input;
		$ui['id_mcal']		= $id_mcal;
		$ui['id_opener']	= $id_opener;
		$ui['js_name']		= $js_name;

		return $this->loadTemplate( 'monthcalendar', $ui );
	}

	/**
	 *	Builds  Range of Years.
	 *	@access		protected
	 *	@return		array
	 */
	protected function buildYears()
	{
		$opt_year	= array();
		switch( $this->type )
		{
			case self::TYPE_FUTURE:
				$start	= $this->year;
				$end	= $this->year + $this->range;
				break;
			case self::TYPE_PAST:
				$start	= $this->year - $this->range;
				$end	= $this->year;
				break;
			default:
				if( $this->range % 2 )
				{
					$start		= $this->year - floor( $this->range / 2 );
					$end		= $this->year + floor( $this->range / 2 );
				}
				else
				{
					$start		= $this->year - floor( ( $this->range - 1 ) / 2 );
					$end		= $this->year + ceil( ( $this->range - 1 ) / 2 );
				}
				$opt_year['_selected'] = $this->year;
				break;
		}

		for( $i=$start; $i<=$end; $i++ )
			$opt_year[$i]	= $i;
		if( !$this->asc )
			krsort( $opt_year );
		return $opt_year;
	}
	
	/**
	 *	Sets Direction of Years.
	 *	@access		public
	 *	@param		bool		$asc		Direction where true is ascending
	 *	@return		void
	 */
	public function setDirection( $asc )
	{
		$this->asc	= (bool)$asc;
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
	 *	Sets new Month Names.
	 *	@access		public
	 *	@param		array		$months		List of Month Names
	 *	@return		void
	 */
	public function setMonths( $months )
	{
		$key	= array_keys( $this->months );
		$months	= array_values( $months );
		$this->months	= array_combine( $key, $months );
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
	 *	Sets Type to 'future' or 'past' or 'present'.
	 *	@access		public
	 *	@param		int			$type		Type of Calendar (TYPE_PAST|TYPE_PRESENT|TYPE_FUTURE)
	 *	@return		void
	 */
	public function setType( $type )
	{
		$this->type	= $type;
	}
}
?>
