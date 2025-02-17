<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Class Methods Information File.
 *
 *	Copyright (c) 2008-2023 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Classes
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Traits;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Traits\Info as TraitInfo;
use CeusMedia\PhpParser\Structure\Trait_ as PhpTrait;
use CeusMedia\PhpParser\Structure\Method_ as PhpMethod;

/**
 *	Builds Class Methods Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Classes
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Methods extends TraitInfo
{
	/**
	 *	Builds View of Class Methods for Class Information File.
	 *	@access		public
	 *	@param		PhpTrait	$trait			Trait Object
	 *	@return		string
	 */
	public function buildView( object $trait ): string
	{
		$this->type	= "trait";

		$list		= [];
		$methods	= $trait->getMethods();
		if( !$methods )
			return "";
		ksort( $methods );
		foreach( $methods as $methodName => $methodData )
			$list[$methodName]	= $this->buildMethodEntry( $trait, $methodData );

		$words		= $this->env->words['classMethods'];
		$heading	= sprintf( $words['heading'], $trait->getName() );
		$data	= array(
			'words'		=> $words,
			'heading'	=> $heading,
			'list'		=> implode( "", $list ),
			'inherited'	=> $this->buildInheritedMethodList( $trait, array_keys( $list ) ),
		);
		return $this->loadTemplate( 'trait.methods', $data );
	}

	/**
	 *	Builds List of inherited Methods of all extended Classes.
	 *	@access		public
	 *	@param		PhpTrait		$trait			Class Object
	 *	@param		array			$got			...
	 *	@return		string
	 */
	private function buildInheritedMethodList( PhpTrait $trait, array $got = [] ): string
	{
		$extended	= [];
		$traits	= $this->getSuperClasses( $trait );
		foreach( $traits as $nr => $class ){
			$list		= [];
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
				$uri		= 'trait.'.$class->getId().".html#class_method_".$methodName;
				$link		= HtmlElements::Link( $uri, $methodName, 'method' );
				$linkTyped	= $this->getTypeMarkUp( $link );
				$got[]		= $methodName;
				$list[$methodName]	= HtmlElements::ListItem( $linkTyped, 0, ['class' => 'method'] );
			}
			if( 0 !== count( $list ) ){
				ksort( $list );
				$list		= HtmlElements::unorderedList( $list );
				$item		= $this->getTypeMarkUp( $class ).$list;
				$attributes	= array( 'class' => 'methodsOfExtendedClass' );
				if( $nr % 3 == 0 )
					$attributes['style']	= "clear: left";										//  line break after each 3 classes
				$extended[]	= HtmlElements::ListItem( $item, 0, $attributes );
			}
		}
		if( !$extended )
			return "";
		$attributes	= array( 'class' => 'extendedClass' );
		$extended	= HtmlElements::unorderedList( $extended, 0, $attributes );
		$data	= [
			'words'	=> $this->words['classMethodsInherited'],
			'list'	=> $extended,
		];
		return $this->loadTemplate( 'trait.methods.inherited', $data );
	}

	/**
	 *	Builds View of a Method with all Information.
	 *	@access		private
	 *	@param		PhpTrait		$trait			Class Object
	 *	@param		PhpMethod		$method			Method Data Object
	 *	@return		string
	 */
	private function buildMethodEntry( PhpTrait $trait, PhpMethod $method ): string
	{
		$attributes	= [];

		$attributes['name']			= $this->buildParamStringList( $method->getName(), 'name' );
		$attributes['description']	= $this->buildParamStringList( str_replace( ['<%', '%>'], ['[%', '%]'], $method->getDescription() ), 'description' );

		$attributes['abstract']		= $this->buildParamList( $method->isAbstract() ? " ": "", 'abstract' );
		$attributes['final']		= $this->buildParamList( $method->isFinal() ? " " : "", 'final' );
		$attributes['static']		= $this->buildParamList( $method->isStatic() ? " " : "", 'static' );

		$accessType	= $method->getAccess() ?: 'unknown';
		$access		= $this->buildAccessLabel( $accessType );

		$attributes['access']		= $this->buildParamStringList( $access, 'access' );
		$attributes['version']		= $this->buildParamStringList( $method->getVersion(), 'version' );
		$attributes['since']		= $this->buildParamStringList( $method->getSince(), 'since' );
		$attributes['copyright']	= $this->buildParamStringList( str_replace( ['<%', '%>'], ['[%', '%]'], $method->getCopyrights() ), 'copyright' );
		$attributes['deprecated']	= $this->buildParamStringList( $method->getDeprecations(), 'deprecated' );
		$attributes['todo']			= $this->buildParamStringList( $method->getTodos(), 'todo' );

		$attributes['author']		= $this->buildParamAuthors( $method );
		$attributes['link']			= $this->buildParamLinkedList( $method->getLinks(), 'link' );
		$attributes['see']			= $this->buildParamLinkedList( $method->getSees(), 'see' );
		$attributes['license']		= $this->buildParamLicenses( $method );

		$attributes['return']		= $this->buildParamReturn( $method );
		$attributes['throws']		= $this->buildParamThrows( $method );
//		$attributes['trigger']		= $this->buildParamTriggers( $method->getTriggers() );

		$params	= [];
		foreach( $method->getParameters() as $parameter ){
			$signature	= $this->getParameterMarkUp( $parameter );
			$text		= $parameter->getDescription() ? '&nbsp;&minus;&nbsp;'.$parameter->getDescription() : "";
			$params[]	= $signature.$text;
		}
		$params	= implode( "<br/>", $params );
		$attributes['param']	= $this->buildParamList( $params, 'param' );

		$attributes	= $this->loadTemplate( 'trait.method.attributes', $attributes );

		$uri		= 'trait.'.$trait->getId().".html#source_class_method_".$method->getName();
		$return		= $method->getReturn() ? $this->getTypeMarkUp( $method->getReturn()->getType() ) : "";
		$methodLink	= HtmlElements::Link( $uri, $method->getName() );
		$methodLink	= '<a href="'.$uri.'" onclick="jumpToLine('.$method->getLine().')">'.$method->getName().'</a>';

		$params	= [];
		foreach( $method->getParameters() as $parameter )
			$params[]	= $this->getParameterMarkUp( $parameter );
		$params	= implode( ", ", $params );
		if( $params	)
			$params	= " ".$params." ";

		$accessType	= $method->getAccess() ?: 'public';

		$data	= array(
			'methodName'	=> $method->getName(),
			'methodTitle'	=> $methodLink,
			'access'		=> $accessType,
			'return'		=> $return,
			'attributes'	=> $attributes,
			'parameters'	=> $params,
			'description'	=> $this->getFormatedDescription( $method->getDescription() ),
		);
		return $this->loadTemplate( 'trait.method', $data );
	}
}

