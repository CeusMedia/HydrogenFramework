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
 *	@package		framework.krypton.core
 *	@extends		Framework_Krypton_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			18.06.2006
 *	@version		0.7
 */
import( 'de.ceus-media.framework.krypton.core.View' );
/**
 *	Generic Definition View with References.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@extends		Framework_Krypton_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			18.06.2006
 *	@version		0.7
 */
class Framework_Krypton_Core_DefinitionView extends Framework_Krypton_Core_View
{
	/**	@var	string			$prefix		Prefix of XML Definition Files */
	protected $prefix			= "";

	protected $definition		= NULL;
	
	protected $inputElements	= NULL;

	/**
	 *	Constructor, references Output Objects.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $useWikiParser = FALSE )
	{
		parent::__construct( $useWikiParser );
		$this->definition	= $this->registry->get( 'definition' );
		$this->loadInputElements();
	}

	/**
	 *	Build Fields of Form Fields.
	 *	@access		public
	 *	@param		string		$fileName			Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$formName			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		string		$languageFile		Name of Language File (e.g. 'example')
	 *	@param		string		$languageSection	Section in Language File (e.g. 'add')
	 *	@param		array		$input				Array of built Input Fields
	 *	@return		array
	 */
	public function buildFields( $fileName, $formName, $languageFile, $languageSection, $inputs )
	{
		$cal_count	= 0;
		$labels		= $this->words[$languageFile][$languageSection];

		$array	= array();
		$this->loadDefinition( $fileName , $formName );
		$fields	= $this->definition->getFields();
		if( count( $fields ) )
		{
			foreach( $fields as $field )
			{
				$data	= $this->definition->getField( $field );
				$suffix	= isset( $labels[$field."_suffix"] ) ? $labels[$field."_suffix"] : "";
				if( isset( $data['calendar'] ) )
				{
					if( $data['calendar']['component'] == "MonthCalendar" )
					{
						import( 'de.ceus-media.framework.krypton.view.component.MonthCalendar' );
						$cal	= new Framework_Krypton_View_Component_MonthCalendar();
						if( isset( $data['calendar']['range'] ) )
							$cal->setRange( $data['calendar']['range'] );
						if( isset( $data['calendar']['type'] ) )
						{
							$type	= Framework_Krypton_View_Component_MonthCalendar::TYPE_PRESENT;
							switch( strtolower( trim( $data['calendar']['type'] ) ))
							{
								case "past":
									$type	= Framework_Krypton_View_Component_MonthCalendar::TYPE_PAST;
									break;
								case "present":
									$type	= Framework_Krypton_View_Component_MonthCalendar::TYPE_PRESENT;
									break;
								case "future":
									$type	= Framework_Krypton_View_Component_MonthCalendar::TYPE_FUTURE;
									break;
							}
							$cal->setType( $type );
						}
						if( isset( $data['calendar']['direction'] ) )
							$cal->setDirection( $data['calendar']['direction'] == "asc" );
						if( isset( $this->words['main']['months'] ) )
							$cal->setMonths( $this->words['main']['months'] );
						$name	= $data['input']['name'];
						$id1	= "mcal".$cal_count;
						$id2	= "mcal_opener".$cal_count;
						$cal	= $cal->buildCalendar($name, $id1, $id2, $id1 );
						$suffix	= $cal."<span class='suffix'>".$suffix."</span>";
						$cal_count++;
					}
					if( $data['calendar']['component'] == "DayCalendar" )
					{
						import( 'de.ceus-media.framework.krypton.view.component.DayCalendar' );
						$cal	= new Framework_Krypton_View_Component_DayCalendar();
				//		if( isset( $data['calendar']['range'] ) )
				//			$cal->setRange( $data['calendar']['range'] );
						if( isset( $data['calendar']['format'] ) )
							$cal->setFormat( $data['calendar']['format'] );
						if( isset( $data['calendar']['type'] ) )
							$cal->setType( $data['calendar']['type'] == "future" ? 1 : ( $data['calendar']['type'] == "past" ? -1 : 0 ) );
				//		if( isset( $data['calendar']['direction'] ) )
				//			$cal->setDirection( $data['calendar']['direction'] == "asc" );
						
				//		$cal->setLanguage( $data['calendar']['language'] );
						$idInput	= $data['input']['name'];
						$idOpener	= "dcal_".$data['input']['name'];
						$cal		= $cal->buildCalendar( $idInput, $idOpener );
						$suffix		= $cal."<span class='suffix'>".$suffix."</span>";
					}
				}
				$colspan	= $data['input']['colspan'] ? $data['input']['colspan'] : 1;
				$class	= 'field';
				if( $data['input']['type'] == "label" && $data['input']['style'] )
					$class = $data['input']['style'];

				$array['field_'.$field]	= $this->html->Field( $data['input']['name'], $inputs['input_'.$field], $class, $suffix, $colspan );
			}
		}
		else
			$this->messenger->noteError( "DefinitionView->buildLabels: no Fields defined for Form '".$formName."'." );
		return $array;
	}

	/**
	 *	Builds Labels and Input Fields of Form widthin Definition.
	 *	@access		public
	 *	@param		string		$fileName			Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$formName			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		string		$languageFile		Name of Language File (e.g. 'example')
	 *	@param		string		$languageSection	Section in Language File (e.g. 'add')
	 *	@param		array		$values				Array of Input Values of defined Fields
	 *	@param		array		$sources			Array of Sources for defined Fields (e.g. Options for Selects)
	 *	@return		array
	 */
	public function buildForm( $fileName, $formName, $languageFile, $languageSection, $values = array(), $sources = array() )
	{
		$fileName	= str_replace( ".", "/", $fileName );
		$this->definition->setForm( $formName );
		$inputs	= $this->buildInputs( $fileName, $formName, $languageFile, $languageSection, $values, $sources );
		$array	= $this->buildLabels( $fileName, $formName, $languageFile, $languageSection )
				+ $this->buildFields( $fileName, $formName, $languageFile, $languageSection, $inputs )
				+ $inputs;
		return (array)$array;
	}
	
	/**
	 *	Build Inputs of Form Fields.
	 *	@access		public
	 *	@param		string		$fileName			Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$formName			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		string		$languageFile		Name of Language File (e.g. 'example')
	 *	@param		string		$languageSection	Section in Language File (e.g. 'add')
	 *	@param		array		$values				Map of available Input Field Values
	 *	@param		array		$sources			Map of Option Arrays for Select Boxes
	 *	@return		array
	 */
	public function buildInputs( $fileName, $formName, $languageFile, $languageSection, $values = array(), $sources = array() )
	{
		$this->loadDefinition( $fileName, $formName );

		$this->inputElements->setFormName( $formName );

		$elements	= array();
		$fields		= $this->definition->getFields();
		foreach( $fields as $fieldName )
		{
			$data	= $this->definition->getField( $fieldName );
			$value	= $this->request->get( $data['input']['name'] );
			$value	= isset( $values[$fieldName] ) ? $values[$fieldName] : $value;
			if( !empty( $data['syntax']['mandatory'] ) )
			{
				$style		= !empty( $data['input']['style'] ) ? $data['input']['style'] : "";
				$classes	= explode( " ", trim( $style ) );
				$classes[]	= "mandatory";
				$data['input']['style']	= implode( " ", $classes );
			}
			$options	= $this->getInputOptions( $fieldName, $data['input'], $languageFile, $sources, $value );
			$input		= $this->inputElements->buildInputElement( $fieldName, $data, $value, $options );
			$elements['input_'.$fieldName]	= $input;
		}
		return $elements;
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
		$key	= $data['input']['label'] ? $data['input']['label'] : $field;
		$label	= $this->html->Label( $data['input']['name'], $labels[$key] );
		return $label;
	}
	
	/**
	 *	Build Labels of Form Fields.
	 *	@access		public
	 *	@param		string		$fileName			Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$formName			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		string		$languageFile		Name of Language File (e.g. 'example')
	 *	@param		string		$languageSection	Section in Language File (e.g. 'add')
	 *	@return		array
	 */
	public function buildLabels( $fileName, $formName, $languageFile, $languageSection )
	{
		$labels		= $this->words[$languageFile][$languageSection];

		$array	= array();
		$this->loadDefinition( $fileName, $formName );
		$fields	= $this->definition->getFields();
		if( count( $fields ) )
		{
			foreach( $fields as $field )
			{
				$data	= $this->definition->getField( $field );
				$classes	= array( 'label');
				if( isset( $data['syntax']['mandatory'] ) && $data['syntax']['mandatory'] )
					$classes[]	= "mandatory";
				$classes	= implode( " ", $classes );
				$label	= $this->getLabel( $data, $field, $labels );
				$label	= $this->html->Label( $data['input']['name'], $label, $classes );
				$array['label_'.$field]	= $label;
			}
		}
		else
			$this->messenger->noteError( "DefinitionView->buildLabels: no Fields defined for Form '".$formName."'." );
		return $array;
	}

	/**
	 *	Returns Map of Options for Input Elements either from Sources Array or Option Language Section depending on Form Definition.
	 *	@access		public
	 *	@param		array		$fieldName			Field Name to build Options for
	 *	@param		array		$inputData			Input Definition of Field
	 *	@param		string		$languageFile		Name of Language File (e.g. 'example')
	 *	@param		array		$sources			Map of Option Arrays for Select Boxes
	 *	@return		array
	 */
	protected function getInputOptions( $fieldName, $inputData, $languageFile, $sources, $value )
	{
		$options	= NULL;
		if( !empty( $inputData['source'] ) )
		{
			$source	= $inputData['source'];
			if( !( isset( $sources[$source] ) && is_array( $sources[$source] ) ) )
				throw new Exception( 'No Options given for "'.$fieldName.'".' );
			$options	= $sources[$source];
		}
		if( !empty( $inputData['options'] ) )
		{
			$optionFile		 = $languageFile;
			$optionSection	 = $inputData['options'];
			if( preg_match( "@[^:]+(:)[^:]+@", $optionSection ) )
			{
				$parts	= explode( ":", $optionSection );
				$optionFile		= $parts[1];
				$optionSection	= $parts[1];
				$this->loadLanguage( $optionFile );
			}
			if( empty( $this->words[$optionFile] ) )
				throw new RuntimeException( 'Language File "'.$optionFile.'" is not loaded.' );
			if( empty( $this->words[$optionFile][$optionSection] ) )
				throw new RuntimeException( 'Option Section "'.$optionSection.'" is missing in Language File "'.$optionFile.'".' );
			$options	= $this->words[$optionFile][$optionSection];
			$options['_selected']	= $value;
		}
		return $options;
	}

	/**
	 *	Returns Field Label including Acronym, ToolTip or HelpHover if available.
	 *	@access		public
	 *	@param		array		$data			Field Definition
	 *	@param		string		$field			Field Key
	 *	@param		array		$labels			Label Pairs
	 *	@return		string
	 */
	public function getLabel( $data, $field, $labels )
	{
		$key	= $data['input']['label'] ? $data['input']['label'] : $field;
		if( isset( $labels[$key] ) )
		{
			$label	= $labels[$key];
			if( isset( $labels[$key."_acronym"] ) )
				$label	= $this->html->Acronym( $label, $labels[$key."_acronym"] );
			else if( isset( $labels[$key."_tip"] ) )
				$label	= $this->html->ToolTip( $label, $labels[$key."_tip"] );
			else if( isset( $labels[$key."_hover"] ) )
				$label	= $this->html->HelpHover( $label, $labels[$key."_hover"] );
			return $label;
		}
		else
			$this->messenger->noteError( "Label for Field '".$field."' is not defined" );
	}

	/**
	 *	Runs Validation of Field Definitions againt Request Input and creates Error Messages.
	 *	@access		protected
	 *	@param		string		$fileName			Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$formName			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@return		void
	 */
	protected function loadDefinition( $fileName, $formName )
	{
		$this->definition->setForm( $formName );
		$this->definition->setPrefix( $this->prefix );
		$this->definition->loadDefinition( $fileName );
	}

	/**
	 *	Loads Class containing Input Element Components, can be overwritten.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadInputElements()
	{
		import( 'de.ceus-media.framework.krypton.view.component.InputElements' );
		$this->inputElements	= new Framework_Krypton_View_Component_InputElements();
	}
}
?>