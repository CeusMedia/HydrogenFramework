<?php
/*class CMF_Hydrogen_View_Helper_HTML{
//	public function __construct();
}

class XHTML extends UI_HTML_Elements {

	const BR = '<br/>';

	static $prefixIdInput	= 'input_';
	static $prefixIdForm	= 'form_';

	static public function Label( $inputName = NULL, $content, $class = NULL ){
		$attributes	= array(
			'for'		=> $inputName === NULL ? NULL : self::$prefixIdInput.$inputName,
			'class'		=> $class,
		);
		return UI_HTML_Tag::create( 'label', $content, $attributes );
	}

	static public function Form( $url, $name, $content ){
		$enctype	= NULL;
		if( substr_count( $content, ' type="file"' ) )
			$enctype	= 'multipart/form-data';
		$attributes	= array(
			'name'		=> $name,
			'action'	=> $url,
			'id'		=> $name === NULL ? NULL : self::$prefixIdForm.$name,
			'method'	=> "post",
			'enctype'	=> $enctype,
		);
		return UI_HTML_Tag::create( 'form', $content, $attributes );
	}
	static public function Select( $name, $options, $class = NULL, $readonly = NULL, $onChange = NULL ){
		if( is_array( $options ) ){
			$selected	= isset( $options['_selected'] ) ? $options['_selected'] : NULL;
			$options	= self::Options( $options, $selected );
		}
		if( preg_match( '/^[a-z0-9_-]+$/i', $onChange ) )
			$onChange	= "document.getElementById('".self::$prefixIdForm.$onChange."').submit();";
		$attributes	= array(
			'id'		=> str_replace( "[]", "", self::$prefixIdInput.$name ),
			'name'		=> $name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
			'multiple'	=> substr( trim( $name ), -2 ) == "[]"	? "multiple" : NULL,
			'onchange'	=> $onChange,
		);
		if( $readonly )
			self::addReadonlyAttributes( $attributes, $readonly );
		return UI_HTML_Tag::create( "select", $options, $attributes );
	}

	static public function Password( $name, $class = NULL, $readonly = NULL ){
		$attributes		= array(
			'type'		=> 'password',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Checkbox( $name, $value, $checked = FALSE, $class = NULL, $readonly = NULL ){
		$attributes	= array(
			'type'		=> 'checkbox',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'value'		=> htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' ),
			'class'		=> $class,
			'checked'	=> $checked ? 'checked' : NULL,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Input( $name, $value, $class = NULL, $readonly = NULL ){
		$attributes	= array(
			'type'		=> 'text',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'value'		=> htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' ),
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function File( $name, $class = NULL, $readonly = NULL ){
		$attributes	= array(
			'type'		=> 'file',
			'name'		=> $name,
			'id'		=> self::$prefixIdInput.$name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Text( $name, $content, $class = NULL, $numberRows = NULL, $readonly = NULL ){
		$content	= htmlspecialchars( $content, ENT_COMPAT, 'UTF-8' );
		$attributes	= array(
			'name'		=> $name,
			'id'		=> self::$prefixIdInput.$name,
			'class'		=> $class,
			'rows'		=> $numberRows,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'textarea', $content, $attributes );
	}

	static public function Li( $content, $class = NULL ){
		return UI_HTML_Tag::create( 'li', $content, array( 'class' => $class ) );
	}

	static public function LiClass( $class, $content ){
		return XHTML::Li( $content, $class );
	}

	static public function Legend( $content, $class = NULL ){
		return UI_HTML_Tag::create( 'legend', $content, array( 'class' => $class ) );
	}

	static public function Fields( $content, $class = NULL ){
		return UI_HTML_Tag::create( 'fieldset', $content, array( 'class' => $class ) );
	}
	
	static public function UlClass( $class, $content ){
		return UI_HTML_Tag::create( 'ul', $content, array( 'class' => $class ) );
	}

	static public function DivID( $id, $content, $attributes = array() ){
		return UI_HTML_Tag::create( 'div', $content, array( 'id' => $id ) );
	}
	
	static public function DivClass( $class, $content = '', $attributes = array() ){
		return UI_HTML_Tag::create( 'div', $content, array( 'class' => $class ) );
	}

	static public function Buttons( $content ){
		return XHTML::DivClass( 'buttonbar',
			$content.
			XHTML::DivClass( 'column-clear' )
		);
	}

	static function Options( $items, $selected = NULL, $keys = array() ){
		if( !count( $items ) )
			return '';
		$first	= array_shift( array_values( $items ) );
		if( is_object( $first ) && count( $keys ) === 2 ){
			$list	= array();
			foreach( $items as $item ){
				$key	= $item->{$keys[0]};
				$label	= $item->{$keys[1]};
				$list[$key]	= $label;
			}
			$items	= $list;
		}
		return UI_HTML_Elements::Options( $items, $selected );
	}

	static public function Heading( $level, $label, $class = NULL ){
		return UI_HTML_Tag::create( 'h'.$level, htmlentities( $label, ENT_COMPAT, 'UTF-8' ), array( 'class' => $class ) );
	}
	
	static public function H2( $label, $class = NULL ){
		return self::Heading( 2, $label, $class );
	}
	
	static public function H3( $label, $class = NULL ){
		return self::Heading( 3, $label, $class );
	}

	static public function H4( $label, $class = NULL ){
		return self::Heading( 4, $label, $class );
	}

	static public function Dl( $definitions ){
		if( is_array( $definitions ) )
			$definitions	= join( $definitions );
		return UI_HTML_Tag::create( 'dl', $definitions );
	}

	static public function Def( $term, $definitions ){
		if( !is_array( $definitions ) )
			$definitions	= array( $definitions );
		foreach( $definitions as $nr => $definition )
			$definitions[$nr]	= UI_HTML_Tag::create( 'dd', $definition );
		$definitions	= join( $definitions );
		
		return UI_HTML_Tag::create( 'dt', $term ).$definitions;
	}
}
 */
?>
