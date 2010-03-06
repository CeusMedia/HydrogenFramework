<?php
/**
 *	Generic Definition View with Language Support.
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
 *	@package		framework.neon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			18.06.2006
 *	@version		0.1
 */
import( 'de.ceus-media.framework.neon.View' );
import( 'de.ceus-media.file.log.Writer' );
/**
 *	Generic Definition View with References.
 *	@category		cmClasses
 *	@package		framework.neon
 *	@extends		Framework_Neon_View
 *	@uses			File_Log_Writer
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			18.06.2006
 *	@version		0.1
 *	@todo			TO BE CLEARED in next Version (see below)
 */
class Framework_Neon_DefinitionView extends Framework_Neon_View
{
	/**	@var	string		$prefix		Prefix of XML Definition Files */
	var $prefix	= "";
	
	/**
	 *	Constructor, references Output Objects.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->definition	=& $this->ref->get( 'definition' );
	}

	/**
	 *	Build Label of Field.
	 *	@access		public
	 *	@param		string		$field			Name of Field
	 *	@return		array
	 *	@todo		TO BE CHANGED in next Version (check Usage with LogFile)
	 *	@todo		sense clear: create simple label, usage unclear: no form, no lan?
	 */
	public function buildLabel( $field )
	{
		$data	= $this->definition->getField( $field );
		$label	= $this->html->Label( $data['input']['name'], $labels[$field] );
		return $label;
		$array['label_'.$field]	= $label;
		$log	= new File_Log_Writer( 'unexpected_usage.log' );
		$log->note( "VIEW field: ".$field );
		return $array;
	}
	
	/**
	 *	Build Labels of Form Fields.
	 *	@access		public
	 *	@param		string		$file				Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$form			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		string		$lan_file			Name of Language File (e.g. 'example')
	 *	@param		string		$lan_section		Section in Language File (e.g. 'add')
	 *	@return		array
	 */
	public function buildLabels( $file , $form, $lan_file, $lan_section )
	{
		$labels		= $this->words[$lan_file][$lan_section];

		$array	= array();
		$this->loadDefinition( $file, $form );
		$fields	= $this->definition->getFields();
		if( count( $fields ) )
		{
			foreach( $fields as $field )
			{
				$data	= $this->definition->getField( $field );
				if( isset( $labels[$field] ) )
				{
					$classes	= array( 'label');
					if( isset( $data['syntax']['mandatory'] ) && $data['syntax']['mandatory'] )
						$classes[]	= "mandatory";
					$classes	= implode( " ", $classes );
					if( isset( $labels[$field."_acronym"] ) )
						$labels[$field]	= $this->html->Acronym( $labels[$field], $labels[$field."_acronym"] );
					else if( isset( $labels[$field."_tip"] ) )
						$labels[$field]	= $this->html->ToolTip( $labels[$field], $labels[$field."_tip"] );
					else if( isset( $labels[$field."_hover"] ) )
						$labels[$field]	= $this->html->HelpHover( $labels[$field], $labels[$field."_hover"] );
					$array['label_'.$field]	= $this->html->Label( $data['input']['name'], $labels[$field], $classes );
				}
				else
					$this->messenger->noteError( "Label for Field '".$field."' is not defined" );
			}
		}
		else
			$this->messenger->noteError( "DefinitionView->buildLabels: no Fields defined for Form '".$form."'." );
		return $array;
	}

	public function buildInputs( $file , $form, $values = array(), $sources = array() )
	{
		$request	= $this->ref->get( 'request' );

		$array	= array();
		$this->loadDefinition( $file , $form );
		$fields	= $this->definition->getFields();
		foreach( $fields as $field )
		{
			$data	= $this->definition->getField( $field );
			if( !isset( $values[$field] ) )
				$values[$field]	= "";
			if( !$values[$field] && $value	= $request->get( $data['input']['name'] ) )
				$values[$field]	= $value;
			if( $data['input']['type'] == "select" )
			{
//				$disabled	= ( isset( $data['input']['disabled'] ) && $data['input']['disabled'] ) ? 'disabled' : false;
				$submit	= isset( $data['input']['submit'] ) && $data['input']['submit'] ? $form : false;
				$input	= $this->html->Select( $data['input']['name'], $sources[$data['input']['source']], $data['input']['style'], false, $submit );
/*				if( is_string( $source ) )
				{
				$words = $language->getWords( $front, $source );
				$ins_options = "";
				foreach( $words as $word_key => $word_value )
				{
					$word_key = substr( $word_key, 4 );
					if( substr( $word_key, 0, 1 ) == "#" ) continue;
					if( $value == $word_key ) $ins_selected = " selected";
					else $ins_selected = "";
					$ins_options .= "<option value='".$word_key."'$ins_selected>".$word_value;
				}
				$field = $gui->elements->Select( $name, $ins_options, $class, $name, false, false, false, $tabindex );
*/			}
			else if( $data['input']['type'] == "textarea" )
				$input = $this->html->TextArea( $data['input']['name'], $values[$field], $data['input']['style'], false, $data['input']['validator'] );
			else if( $data['input']['type'] == "input" )
			{
				if( isset( $data['calendar'] ) && $values[$field] )
					$values[$field]	= date( $data['calendar']['format_php'], $values[$field] );
				$maxlength	= isset( $data['syntax']['maxlength'] ) ? $data['syntax']['maxlength'] : 0;
				$validator	= isset( $data['input']['validator'] ) ? $data['input']['validator'] : "";
				$style		= isset( $data['input']['style'] ) ? $data['input']['style'] : "";
				$input = $this->html->Input( $data['input']['name'], $values[$field], $style, false, false, false, $maxlength, $validator );
			}
			else if( $data['input']['type'] == "password" )
				$input = $this->html->Password( $data['input']['name'], $data['input']['style'] );
			else if( $data['input']['type'] == "checkbox" )
				$input = $this->html->CheckBox( $data['input']['name'], 1, $values[$field], $data['input']['style'] );
			else if( $data['input']['type'] == "file" )
				$input = $this->html->File( $data['input']['name'], '', $data['input']['style'] );
			else if( $data['input']['type'] == "label" )
			{
//				$input = $values[$field];
				$value	= $values[$field];
				if( isset( $data['input']['style'] ) && $data['input']['style'] )
					$input	= '<span class="'.$data['input']['style'].'">'.$value.'</span>';
				else
					$input	= '<span>'.$value.'</span>';
			}
			else if( $data['input']['type'] == "radiogroup" )
			{
				$input = $this->html->RadioGroup( $data['input']['name'], $sources[$data['input']['source']], $data['input']['style'] );
			}

/*			else if( $data['input']['type'] == "checklabel" )
			{
				$checkbox = $gui->elements->CheckBox( $name, $value, $name );
				$field = $gui->elements->CheckLabel( $checkbox, $source, $class, $name, $maxlength );
			}
			else if( $data['input']['type'] == "radio" && $source )
			{
				$field = $gui->elements->Radio( $name, $value, array( $source ) );
			}
			else if( $data['input']['type'] == "radios" && $source )
			{
				$words = $language->getWords( $front, $source );
				foreach( $words as $word_key => $word_value )
				{
					unset( $words[$word_key] );
					$word_key = substr( $word_key, 4 );
					$new_words[$word_key] = $word_value;
				}
				$field = $gui->elements->Radio( $name, $value, $new_words, $class, false, false, $allow, $tabindex, $disabled || $radio_dis );
*/			
			$array['input_'.$field]	= $input;
		}
		return $array;
	}

	public function buildFields( $file , $form, $lan_file, $lan_section, $inputs )
	{
		$labels		= $this->words[$lan_file][$lan_section];

		$array	= array();
		$this->loadDefinition( $file , $form );
		$fields	= $this->definition->getFields();
		if( count( $fields ) )
		{
			foreach( $fields as $field )
			{
				$data	= $this->definition->getField( $field );
				if( isset( $data['calendar'] ) )
				{
					if( $data['calendar']['component'] == "MonthCalendar" )
					{
						import( 'de.ceus-media.framework.neon.views.components.MonthCalendar' );
						$cal	= new MonthCalendar();							if( isset( $data['calendar']['range'] ) )
							$cal->setRange( $data['calendar']['range'] );
						if( isset( $data['calendar']['type'] ) )
							$cal->setType( $data['calendar']['type'] );
						if( isset( $data['calendar']['direction'] ) )
							$cal->setDirection( $data['calendar']['direction'] == "asc" );
						$name	= $data['input']['name'];
						$id1	= "mcal".$cal_count;
						$id2	= "mcal_opener".$cal_count;
						$cal	= $cal->buildCalendar($name, $id1, $id2, $id1 );
						$inputs['input_'.$field]	.= $cal;
						$cal_count++;
					}
					if( $data['calendar']['component'] == "DayCalendar" )
					{
						import( 'de.ceus-media.framework.neon.views.components.DayCalendar' );
						$cal	= new DayCalendar();
						if( isset( $data['calendar']['range'] ) )
							$cal->setRange( $data['calendar']['range'] );
						if( isset( $data['calendar']['format_js'] ) )
							$cal->setFormat( $data['calendar']['format_js'] );
						if( isset( $data['calendar']['type'] ) )
							$cal->setType( $data['calendar']['type'] );
				//		if( isset( $data['calendar']['direction'] ) )
				//			$cal->setDirection( $data['calendar']['direction'] == "asc" );
						
				//		$cal->setLanguage( $data['calendar']['language'] );
						$id_input	= $data['input']['name'];
						$id_opener	= "dcal_".$data['input']['name'];
						$cal	= $cal->buildCalendar( $id_input, $id_opener );
						$inputs['input_'.$field]	.= $cal;
					}
				}
				$suffix	= isset( $labels[$field."_suffix"] ) ? $labels[$field."_suffix"] : "";
				$colspan	= $data['input']['colspan'] ? $data['input']['colspan'] : 1;
				$class	= 'field';
				if( $data['input']['type'] == "label" && $data['input']['style'] )
					$class = $data['input']['style'];

				$array['field_'.$field]	= $this->html->Field( $data['input']['name'], $inputs['input_'.$field], $class, $suffix, $colspan );
			}
		}
		else
			$this->messenger->noteError( "DefinitionView->buildLabels: no Fields defined for Form '".$form."'." );
		return $array;
	}

	/**
	 *	Builds Labels and Input Fields of Form widthin Definition.
	 *	@access		public
	 *	@param		string		$file				Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$form				Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		string		$lan_file			Name of Language File (e.g. 'example')
	 *	@param		string		$lan_section		Section in Language File (e.g. 'add')
	 *	@param		array		$values				Array of Input Values of defined Fields
	 *	@param		array		$sources			Array of Sources for defined Fields (e.g. Options for Selects)
	 *	@return		array
	 */
	public function buildForm( $file , $form, $lan_file, $lan_section, $values = array(), $sources = array() )
	{
		$this->definition->setForm( $form );
		$inputs	= $this->buildInputs( $file, $form, $values, $sources );
		$array	= $this->buildLabels( $file, $form, $lan_file, $lan_section )
				+ $this->buildFields( $file, $form, $lan_file, $lan_section, $inputs )
				+ $inputs;
		return (array)$array;
	}

	/**
	 *	Loads Form Definition.
	 *	@access		protected
	 *	@param		string		$file				Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$form				Form Name in  XML Definition File
	 *	@return		void
	 */
	protected function loadDefinition( $file, $form )
	{
		$this->definition->setForm( $form );
		$this->definition->setOption( 'prefix', $this->prefix );
		$this->definition->loadDefinition( $file );
	}
}
?>