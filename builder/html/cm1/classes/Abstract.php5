<?php
/**
 *	General Builder Class with useful Methods for inheriting Classes.
 *
 *	Copyright (c) 2008-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Abstract.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.ui.html.Elements' );
/**
 *	General Builder Class with useful Methods for inheriting Classes.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1
 *	@uses			UI_HTML_Elements
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Abstract.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 *	@todo			Code Doc
 */
abstract class Builder_HTML_CM1_Abstract
{
	/**	@var		Environment		$env 		Environment Object */
	protected $env;
	/**	@var		string			$type		Data Type (class|file) */
	protected $type		= NULL;
	/**	@var		array			$words		Array of Language Pairs */
	protected $words;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env 		Environment Object
	 *	@param		string			$type		Data Type (class|file)
	 *	@return		void
	 */
	public function __construct( Environment $env, $type = NULL )
	{
		$this->env		= $env;
		$this->type		= $type;
		$this->words	= $this->env->words;
	}

	/**
	 *	Builds Authors Entry for Parameters List.
	 *	@access		protected
	 *	@param		array			$data		Authors Data Array
	 *	@param		array			$list		Result List of Authors, empty but can be preset
	 *	@return		string
	 */
	protected function buildParamAuthors( $data, $list = array() )
	{
		foreach( $data->getAuthors() as $author )
		{
			if( $author->getEmail() )
				$author	= UI_HTML_Elements::Link( "mailto:".$author->getEmail(), $author->getName(), $this->type.'-info-author' );
			else
				$author	= $author->getName();
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $author ) );
		}
		return $this->buildParamList( $list, 'authors' );
	}

	protected function buildParamLinkedList( $data, $key, $list = array() )
	{
		foreach( $data as $url )
		{
			$link	= UI_HTML_Elements::Link( $url, $url, $this->type.'-info-link' );
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $link ) );
		}
		return $this->buildParamList( $list, $key );
	}
	
	protected function buildParamList( $list, $title )
	{
		$type	= 'param'.ucFirst( $title );
		if( !$list )
			return "";
		if( is_array( $list ) )
		{
			$data	= array(
				'list'					=> implode( "\n", $list ),
				(string) $this->type	=> $type
			);
			$list	= $this->loadTemplate( $this->type.'.info.param.list', $data );
		}
		$data	= array(
			'key'	=> $this->words[$this->type.'Info'][$type],
			'value'	=> $list,
			'class'	=> $title,
		);
		return $this->loadTemplate( $this->type.'.info.param', $data );
	}

	protected function buildParamStringList( $value, $key, $list = array() )
	{
		if( !is_array( $value ) )
			return $this->buildParamList( $value, $key );
		foreach( $value as $label )
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $label ) );
		return $this->buildParamList( $list, $key );
	}

	protected function getParameterMarkUp( Model_Parameter $data )
	{
		$name		= $data->getName();
		$name		= $data->isReference() ? "&amp;&nbsp;$".$name : "$".$name;
		$name		= $data->getDefault() ? '<small>['.$name.']</small>' : $name;
		if( $data->getDescription() )
			$name	= UI_HTML_Elements::Acronym( $name, $data->getDescription() );

		$type		= $data->getCast() ? $data->getCast() : ( $data->getType() ? $data->getType() : "unknown" );
		$type		= $this->getTypeMarkUp( $type );
		$default	= $data->getDefault() ? '<span class="default"> = '.$data->getDefault().'</span>' : "";
		$code		= $type.' '.$name.$default;
		return $code;
	}

	protected function getTypeMarkUp( $type, $inPackage = FALSE )
	{
		if( !$type )
			throw new Exception( 'Type cannot be empty' );
		$label	= $type;
		if( is_object( $type ) )
		{
			$url	= $this->getUrlFromClass( $type );
			$label	= UI_HTML_Elements::Link( $url, $type->getName() );
		}
		else if( is_string( $type ) )
		{
			if( in_array( $type, $this->env->phpClasses ) )
			{
				$url	= "http://us3.php.net/manual/en/class.".strtolower( $type ).".php";
				$label	= UI_HTML_Elements::Link( $url, "PHP: ".$type );
			}
			else if( array_key_exists( $type, $this->env->words['types'] ) )
			{
				$url	= "http://us3.php.net/manual/en/language.types.".strtolower( $type ).".php";
				$label	= UI_HTML_Elements::Link( $url, $label );
				$label	= UI_HTML_Elements::Acronym( $label, $this->env->words['types'][$type] );
			}
			else if( array_key_exists( $type, $this->env->words['pseudoTypes'] ) )
			{
				$url	= "http://us3.php.net/manual/en/language.pseudo-types.php#language.types.".strtolower( $type );
				if( $type == "dotdotdot" )
					$label	= "...";
				$label	= UI_HTML_Elements::Link( $url, $label );
				$label	= UI_HTML_Elements::Acronym( $label, $this->env->words['pseudoTypes'][$type] );
			}
#			else if( $type !== "unknown" )
#				remark( "!getTypeMarkUp: ".$type );
		}
		return UI_HTML_Tag::create( 'span', $label, array( 'class' => 'type' ) );
	}

	public function getUrlFromClass( Model_Interface $class )
	{
		switch( get_class( $class ) )
		{
			case 'Model_Class':		return "class.".$class->getId().".html";
			case 'Model_Interface':	return "interface.".$class->getId().".html";
			default:				throw new Exception( 'Invalid class' );
		}
	}
	
	public function getUrlFromClassName( $className, Model_Interface $relatedClass )
	{
		$class	= $this->env->data->getClassFromClassName( $className, $relatedClass );
		if( is_object( $class ) )
			return $this->getUrlFromClass( $class );
		
		remark( "!!!getUrlFromClassName:not found" );
		return NULL;
	}

	protected function hasTemplate( $fileKey )
	{
		return $this->env->hasTemplate( $fileKey );
	}
	
	/**
	 *	Loads a Template and inserts Data.
	 *	@access		public
	 *	@param		string			$fileKey		Key of Template, e.g. folder.file.sub for themes/.../templates/folder/file.sub.html
	 *	@param		array			$data			Data Array to insert into Template
	 *	@return		string
	 */
	protected function loadTemplate( $fileKey, $data )
	{
		return $this->env->loadTemplate( $fileKey, $data );
	}
}
?>