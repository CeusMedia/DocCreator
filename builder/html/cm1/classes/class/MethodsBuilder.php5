<?php
/**
 *	Builds Class Methods Information File.
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
 *	@package		DocCreator_Builder_HTML_CM1_Class
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: MethodsBuilder.php5 740 2009-10-24 00:04:50Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.class.InfoBuilder' );
/**
 *	Builds Class Methods Information File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Class
 *	@extends		Builder_HTML_CM1_Class_InfoBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: MethodsBuilder.php5 740 2009-10-24 00:04:50Z christian.wuerker $
 */
class Builder_HTML_CM1_Class_MethodsBuilder extends Builder_HTML_CM1_Class_InfoBuilder
{
	/**
	 *	Builds View of Class Methods for Class Information File.
	 *	@access		public
	 *	@param		ADT_PHP_Class		$class			Class Object
	 *	@return		string
	 */
	public function buildView( ADT_PHP_Class $class )
	{
		$this->type	= "class";
		
		$list		= array();
		$methods	= $class->getMethods();
		if( !$methods )
			return "";
		ksort( $methods );
		foreach( $methods as $methodName => $methodData )
			$list[$methodName]	= $this->buildMethodEntry( $class, $methodData );

		$words		= $this->env->words['classMethods'];
		$heading	= sprintf( $words['heading'], $class->getName() );
		$data	= array(
			'words'		=> $words,
			'heading'	=> $heading,
			'list'		=> implode( "", $list ),
			'inherited'	=> $this->buildInheritedMethodList( $class, array_keys( $list ) ),
		);
		return $this->loadTemplate( 'class.methods', $data );
	}

	/**
	 *	Builds List of inherited Methods of all extended Classes.
	 *	@access		public
	 *	@param		ADT_PHP_Class		$class			Class Object
	 *	@return		string
	 */
	private function buildInheritedMethodList( ADT_PHP_Class $class, $got = array() )
	{	
		$extended	= array();
		$methods	= array_keys( $class->getMethods() );
		$classes	= $this->getSuperClasses( $class );
		foreach( $classes as $class )
		{
			$list		= array();
			if( !is_object( $class ) )
				continue;
			foreach( $class->getMethods() as $methodName => $methodData )
			{
				if( in_array( $methodName, $got ) )
					continue;
				if( $methodData->getAccess() == "private" )
					continue;
				if( $methodData->isFinal() )
					continue;
				if( $methodData->isAbstract() )
					continue;
				$uri		= 'class.'.$class->getId().".html#class_method_".$methodName;
				$link		= UI_HTML_Elements::Link( $uri, $methodName );
				$got[]		= $methodName;
				$list[$methodName]	= UI_HTML_Elements::ListItem( $link );
			}
			if( $list )
			{
				ksort( $list );
				$list		= UI_HTML_Elements::unorderedList( $list );
				$item		= $this->getTypeMarkUp( $class ).$list;
				$attributes	= array( 'class' => 'methodsOfExtendedClass' );
				$extended[]	= UI_HTML_Elements::ListItem( $item, 0, $attributes );
			}
		}
		if( !$extended )
			return "";
		$attributes	= array( 'class' => 'extendedClass' );
		$extended	= UI_HTML_Elements::unorderedList( $extended, 0, $attributes );
		$data	= array(
			'words'	=> $this->words['classMethodsInherited'],
			'list'	=> $extended,
		);
		return $this->loadTemplate( 'class.methods.inherited', $data );
	}

	/**
	 *	Builds View of a Method with all Information.
	 *	@access		private
	 *	@param		ADT_PHP_Class		$class			Class Object
	 *	@param		ADT_PHP_Method		$method			Method Data Object
	 *	@return		string
	 */
	private function buildMethodEntry( ADT_PHP_Class $class, ADT_PHP_Method $method )
	{
		$attributes	= array();

		$attributes['name']			= $this->buildParamStringList( $method->getName(), 'name' );
		$attributes['description']	= $this->buildParamStringList( $method->getDescription(), 'description' );


		$attributes['abstract']		= $this->buildParamList( $method->isAbstract() ? " ": "", 'abstract' );
		$attributes['final']		= $this->buildParamList( $method->isFinal() ? " " : "", 'final' );
		$attributes['static']		= $this->buildParamList( $method->isStatic() ? " " : "", 'static' );

		$access		= $method->getAccess() ? $method->getAccess() : 'public';
		$attributes['access']		= $this->buildParamStringList( $access, 'access' );
		$attributes['version']		= $this->buildParamStringList( $method->getVersion(), 'version' );
		$attributes['since']		= $this->buildParamStringList( $method->getSince(), 'since' );
		$attributes['copyright']	= $this->buildParamStringList( $method->getCopyright(), 'copyright' );
		$attributes['deprecated']	= $this->buildParamStringList( $method->getDeprecations(), 'deprecated' );
		$attributes['todo']			= $this->buildParamStringList( $method->getTodos(), 'todo' );

		$attributes['author']		= $this->buildParamAuthors( $method );
		$attributes['link']			= $this->buildParamLinkedList( $method->getLinks(), 'link' );
		$attributes['see']			= $this->buildParamLinkedList( $method->getSees(), 'see' );
		$attributes['license']		= $this->buildParamLicenses( $method );

		$attributes['return']		= $this->buildParamReturn( $method );
		$attributes['throws']		= $this->buildParamThrows( $method );

		$params	= array();
		foreach( $method->getParameters() as $parameter )
		{
			$signature	= $this->getParameterMarkUp( $parameter );
			$text		= $parameter->getDescription() ? '&nbsp;&minus;&nbsp;'.$parameter->getDescription() : "";
			$params[]	= $signature.$text;
		}
		$params	= implode( "<br/>", $params );	
		$attributes['param']	= $this->buildParamList( $params, 'param' );

		$attributes	= $this->loadTemplate( 'class.method.attributes', $attributes );

		$uri		= 'class.'.$class->getId().".html#source_class_method_".$method->getName();
		$return		= $method->getReturn() ? $this->getTypeMarkUp( $method->getReturn()->getType() ) : "";
		$methodLink	= UI_HTML_Elements::Link( $uri, $method->getName() );

		$params	= array();
		foreach( $method->getParameters() as $parameter )
			$params[]	= $this->getParameterMarkUp( $parameter );
		$params	= implode( ", ", $params );	
		if( $params	)
			$params	= " ".$params." ";
		
		$access		= $method->getAccess() ? $method->getAccess() : 'public';
		$data	= array(
			'methodName'	=> $method->getName(),
			'methodTitle'	=> $methodLink,
			'access'		=> $access,
			'return'		=> $return,
			'attributes'	=> $attributes,
			'parameters'	=> $params,
			'description'	=> nl2br( trim( $method->getDescription() ) ),
		);
		return $this->loadTemplate( 'class.method', $data );
	}
}
?>