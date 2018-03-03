<?php
/**
 *	Builds Class Methods Information File.
 *
 *	Copyright (c) 2008-2015 Christian Würker (ceusmedia.de)
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
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Class
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Methods.php5 82 2011-10-03 00:45:13Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\Classes;
/**
 *	Builds Class Methods Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Class
 *	@extends		DocCreator_Builder_HTML_Class_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Methods.php5 82 2011-10-03 00:45:13Z christian.wuerker $
 */
class Methods extends \CeusMedia\DocCreator\Builder\HTML\Classes\Info{

	/**
	 *	Builds List of inherited Methods of all extended Classes.
	 *	@access		public
	 *	@param		ADT_PHP_Class		$class			Class Object
	 *	@return		string
	 */
	private function buildInheritedMethodList( \ADT_PHP_Class $class, $got = array() ){
		$extended	= array();
		$classes	= $this->getSuperClasses( $class );
		foreach( $classes as $nr => $class ){
			$list		= array();
			if( !is_object( $class ) )
				continue;
			foreach( $class->getMethods() as $methodName => $methodData ){
				if( in_array( $methodName, $got ) )
					continue;
				if( $methodData->getAccess() == "private" )
					continue;
				if( $methodData->isFinal() )
					continue;
				if( $methodData->isAbstract() )
					continue;
				$uri		= 'class.'.$class->getId().".html#class_method_".$methodName;
				$link		= \UI_HTML_Elements::Link( $uri, $methodName, 'method' );
				$linkTyped	= $this->getTypeMarkUp( $link );
				$got[]		= $methodName;
				$list[$methodName]	= \UI_HTML_Elements::ListItem( $linkTyped, 0, array( 'class' => 'method' ) );
			}
			if( $list ){
				ksort( $list );
				$list		= \UI_HTML_Elements::unorderedList( $list );
				$item		= $this->getTypeMarkUp( $class ).$list;
				$attributes	= array( 'class' => 'methodsOfExtendedClass' );
				if( $nr % 3 == 0 )
					$attributes['style']	= "clear: left";										//  line break after each 3 classes
				$extended[]	= \UI_HTML_Elements::ListItem( $item, 0, $attributes );
			}
		}
		if( !$extended )
			return "";
		$attributes	= array( 'class' => 'extendedClass' );
		$extended	= \UI_HTML_Elements::unorderedList( $extended, 0, $attributes );
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
	private function buildMethodEntry( \ADT_PHP_Class $class, \ADT_PHP_Method $method ){
		$attributes	= array();

		$attributes['name']			= $this->buildParamStringList( $method->getName(), 'name' );
		$attributes['description']	= $this->buildParamStringList( str_replace( array( '<%', '%>' ), array( '[%', '%]' ), $method->getDescription() ), 'description' );

		$attributes['abstract']		= $this->buildParamList( $method->isAbstract() ? " ": "", 'abstract' );
		$attributes['final']		= $this->buildParamList( $method->isFinal() ? " " : "", 'final' );
		$attributes['static']		= $this->buildParamList( $method->isStatic() ? " " : "", 'static' );

		$accessType	= $method->getAccess() ? $method->getAccess() : 'unknown';
		$access		= $this->buildAccessLabel( $accessType );

		$attributes['access']		= $this->buildParamStringList( $access, 'access' );
		$attributes['version']		= $this->buildParamStringList( $method->getVersion(), 'version' );
		$attributes['since']		= $this->buildParamStringList( $method->getSince(), 'since' );
		$attributes['copyright']	= $this->buildParamStringList( str_replace( array( '<%', '%>' ), array( '[%', '%]' ), $method->getCopyright() ), 'copyright' );
		$attributes['deprecated']	= $this->buildParamStringList( $method->getDeprecations(), 'deprecated' );
		$attributes['todo']			= $this->buildParamStringList( $method->getTodos(), 'todo' );

		$attributes['author']		= $this->buildParamAuthors( $method );
		$attributes['link']			= $this->buildParamLinkedList( $method->getLinks(), 'link' );
		$attributes['see']			= $this->buildParamLinkedList( $method->getSees(), 'see' );
		$attributes['license']		= $this->buildParamLicenses( $method );

		$attributes['return']		= $this->buildParamReturn( $method );
		$attributes['throws']		= $this->buildParamThrows( $method );
//		$attributes['trigger']		= $this->buildParamTriggers( $method->getTriggers() );

		$params	= array();
		foreach( $method->getParameters() as $parameter ){
			$signature	= $this->getParameterMarkUp( $parameter );
			$text		= $parameter->getDescription() ? '&nbsp;&minus;&nbsp;'.$parameter->getDescription() : "";
			$params[]	= $signature.$text;
		}
		$params	= implode( "<br/>", $params );
		$attributes['param']	= $this->buildParamList( $params, 'param' );

		$attributes	= $this->loadTemplate( 'class.method.attributes', $attributes );

		$uri		= 'class.'.$class->getId().".html#source_class_method_".$method->getName();
		$return		= $method->getReturn() ? $this->getTypeMarkUp( $method->getReturn()->getType() ) : "";
		$methodLink	= \UI_HTML_Elements::Link( $uri, $method->getName() );
		$methodLink	= '<a href="'.$uri.'" onclick="jumpToLine('.$method->getLine().')">'.$method->getName().'</a>';

		$params	= array();
		foreach( $method->getParameters() as $parameter )
			$params[]	= $this->getParameterMarkUp( $parameter );
		$params	= implode( ", ", $params );
		if( $params	)
			$params	= " ".$params." ";

		$accessType	= $method->getAccess() ? $method->getAccess() : 'public';

		$data	= array(
			'methodName'	=> $method->getName(),
			'methodTitle'	=> $methodLink,
			'access'		=> $accessType,
			'return'		=> $return,
			'attributes'	=> $attributes,
			'parameters'	=> $params,
			'description'	=> $this->getFormatedDescription( $method->getDescription() ),
		);
		return $this->loadTemplate( 'class.method', $data );
	}

	/**
	 *	Builds View of Class Methods for Class Information File.
	 *	@access		public
	 *	@param		ADT_PHP_Interface	$class			Class Object
	 *	@return		string
	 */
	public function buildView( \ADT_PHP_Interface $class ){
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
}
?>
