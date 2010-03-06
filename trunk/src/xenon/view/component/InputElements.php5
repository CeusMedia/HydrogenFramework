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
class Framework_Xenon_View_Component_InputElements extends Framework_Xenon_Core_View
{
	protected $registeredInputElements	= array(
		'select'		=> "buildInputElementSelect",
		'textarea'		=> 'buildInputElementTextarea',
		'input'			=> 'buildInputElementInput',
		'password'		=> 'buildInputElementPassword',
		'checkbox'		=> 'buildInputElementCheckBox',
		'checklabel'	=> 'buildInputElementCheckLabel',
		'file'			=> 'buildInputElementFile',
		'label'			=> 'buildInputElementLabel',
#		'radio'			=> 'buildInputElementRadio',
		'radiogroup'	=> 'buildInputElementRadioGroup',
		'radiolist'		=> 'buildInputElementRadioList',
#		'checklabel'	=> 'buildInputElementCheckLabel',
	);

	public function __construct( $formName = NULL )
	{
		parent::__construct();
		if( $formName )
			$this->setFormName( $formName );
#		$this->languageFile		= $languageFile;
#		$this->languageSection	= $languageSection;
#		$this->labels			= $this->words[$languageFile][$languageSection];
	}
	
	public function buildInputElement( $fieldName, $fieldData, $value, $options = NULL )
	{
		//  --  LOOK UP INPUT ELEMENT METHOD  --  //
		if( !array_key_exists( $fieldData['input']['type'], $this->registeredInputElements ) )
			throw new BadMethodCallException( 'No Method defined for Input Type "'.$fieldData['input']['type'].'".' );

		$method	= $this->registeredInputElements[$fieldData['input']['type']];
		return $this->$method( $fieldData, $value, $options );
	}

	protected function buildInputElementSelect( $fieldData, $value, $options = NULL )
	{
		$input		= $fieldData['input'];
//		$disabled	= ( isset( $input['disabled'] ) && $input['disabled'] ) ? 'disabled' : false;
		$submit		= isset( $input['submit'] ) && $input['submit'] ? $this->formName : FALSE;
		$input		= $this->html->Select( $input['name'], $options, $input['style'], FALSE, $submit );
		return $input;
	}

	protected function buildInputElementTextarea( $fieldData, $value )
	{
		$input	= $fieldData['input'];
		$input	= $this->html->TextArea( $input['name'], $value, $input['style'], $input['disabled'], $input['validator'] );
		return $input;
	}

	protected function buildInputElementInput( $fieldData, $value )
	{
		$input		= $fieldData['input'];
		$syntax		= $fieldData['syntax'];
		
		$maxlength	= !empty( $syntax['maxlength'] )	? $syntax['maxlength'] : NULL;
		$validator	= !empty( $input['validator'] )		? $input['validator'] : NULL;
		$style		= !empty( $input['style'] )			? $input['style'] : NULL;
		$input 		= $this->html->Input( $input['name'], $value, $style, FALSE, FALSE, $maxlength, $validator );
		return $input;
	}

	protected function buildInputElementPassword( $fieldData, $value )
	{
		return $this->html->Password( $fieldData['input']['name'], $fieldData['input']['style'] );
	}		
		
	protected function buildInputElementCheckBox( $fieldData, $value )
	{
		$input = $fieldData['input'];
		$input = $this->html->CheckBox( $input['name'], 1, $value, $input['style'] );
		return $input;
	}

/*	protected function buildInputElementCheckLabel( $fieldData, $value )
	{
		$input		= $fieldData['input'];
		$labels		= $this->words[$languageFile][$languageSection];
		$label		= $this->getLabel( $data, $fieldName, $this->labels );
		$input		= $this->html->CheckLabel( $input['name'], $value, $label, $input['style'] );
		return $input;
	}*/

	protected function buildInputElementFile( $fieldData )
	{
		return $this->html->File( $fieldData['input']['name'], '', $fieldData['input']['style'] );
	}

	protected function buildInputElementLabel( $fieldData, $value )
	{
		$input	= $fieldData['input'];
		$class	= !empty( $fieldData['style'] ) ? $fieldData['style'] : NULL;
		$input	= UI_HTML_Tag::create( 'span', $value, array( 'class' => $class ) );
		return $input;
	}

/*	protected function buildInputElementRadio( $fieldData, $value )
	{
		$input = $fieldData['input'];
		return $this->html->Radio( $input['name'], $value, array( $source ) );
	}*/


	protected function buildInputElementRadioGroup( $fieldData, $value, $options )
	{
		$input	= $fieldData['input'];
		$input	= $this->html->RadioGroup( $input['name'], $options, $input['style'] );
		return $input;
	}
	
	protected function buildInputElementRadioList( $fieldData, $value, $options )
	{
		$input	= $fieldData['input'];
		$input	= $this->html->RadioList( $input['name'], $options, $input['style'] );
		return $input;
	}
	
	/**
	 *	@todo		migrate older Input Elements
	 */
/*	protected function buildInputElementOthersToMigrate( $fieldData, $value )
	{
#			else if( $data['input']['type'] == "checklabel" )
#			{
#				$checkbox = $gui->elements->CheckBox( $name, $value, $name );
#				$field = $gui->elements->CheckLabel( $checkbox, $source, $class, $name, $maxlength );
#			}

#			else if( $data['input']['type'] == "selectlabel" )
#			{
#				$options	= $this->words[$languageFile][$data['input']['source']];
#				$input = $options[$values[$fieldName]];
#			}
			
			
#			else if( $data['input']['type'] == "radios" && $source )
#			{
#				$words = $language->getWords( $front, $source );
#				foreach( $words as $word_key => $word_value )
#				{
#					unset( $words[$word_key] );
#					$word_key = substr( $word_key, 4 );
#					$new_words[$word_key] = $word_value;
#				}
#				$field = $gui->elements->Radio( $name, $value, $new_words, $class, false, false, $allow, $tabindex, $disabled || $radio_dis );
	}*/
	
	public function setFormName( $formName )
	{
		$this->formName			= $formName;
	}
}
?>