<?php
/**
 *	Builds Interface Methods Information File.
 *
 *	Copyright (c) 2008-2021 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interface
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\Interfaces;

use CeusMedia\DocCreator\Builder\HTML\Interfaces\Info as InterfaceInfo;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;
use CeusMedia\PhpParser\Structure\Method_ as PhpMethod;

use UI_HTML_Elements as HtmlElements;

/**
 *	Builds Interface Methods Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interface
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Methods extends InterfaceInfo
{
	/**
	 *	Builds List of inherited Methods of all extended Interfaces.
	 *	@access		public
	 *	@param		PhpInterface		$interface			Interface Object
	 *	@param		array				$got				...
	 *	@return		string
	 */
	private function buildInheritedMethodList( PhpInterface $interface, $got = array() ): string
	{
		$extended	= array();
		$interfaces	= $this->getSuperInterfaces( $interface );
		foreach( $interfaces as $interface ){
			$list		= array();
			if( !is_object( $interface ) )
				continue;
			foreach( $interface->getMethods() as $methodName => $methodData ){
				if( in_array( $methodName, $got ) )
					continue;
				if( $methodData->getAccess() == "private" )
					continue;
				if( $methodData->isFinal() )
					continue;
				if( $methodData->isAbstract() )
					continue;
				$uri		= 'interface.'.$interface->getId().".html#interface_method_".$methodName;
				$link		= HtmlElements::Link( $uri, $methodName, 'method' );
				$linkTyped	= $this->getTypeMarkUp( $link );
				$got[]		= $methodName;
				$list[$methodName]	= HtmlElements::ListItem( $linkTyped, 0, array( 'class' => 'method' ) );
			}
			if( $list ){
				ksort( $list );
				$list		= HtmlElements::unorderedList( $list );
				$item		= $this->getTypeMarkUp( $interface ).$list;
				$attributes	= array( 'class' => 'methodsOfExtendedInterface' );
				$extended[]	= HtmlElements::ListItem( $item, 0, $attributes );
			}
		}
		if( !$extended )
			return "";
		$attributes	= array( 'class' => 'extendedInterface' );
		$extended	= HtmlElements::unorderedList( $extended, 0, $attributes );
		$data	= [
			'words'	=> $this->words['interfaceMethodsInherited'],
			'list'	=> $extended,
		];
		return $this->loadTemplate( 'interface.methods.inherited', $data );
	}

	/**
	 *	Builds View of a Method with all Information.
	 *	@access		private
	 *	@param		PhpInterface		$interface			Interface Object
	 *	@param		PhpMethod		$method			Method Data Object
	 *	@return		string
	 */
	private function buildMethodEntry( PhpInterface $interface, PhpMethod $method ): string
	{
		$attributes	= array();

		$attributes['name']			= $this->buildParamStringList( $method->getName(), 'name' );
		$attributes['description']	= $this->buildParamStringList( $method->getDescription(), 'description' );

		$attributes['abstract']		= $this->buildParamList( $method->isAbstract() ? " ": "", 'abstract' );
		$attributes['final']		= $this->buildParamList( $method->isFinal() ? " " : "", 'final' );
		$attributes['static']		= $this->buildParamList( $method->isStatic() ? " " : "", 'static' );

		$accessType	= $method->getAccess() ? $method->getAccess() : 'unknown';
		$access		= $this->buildAccessLabel( $accessType );

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
		foreach( $method->getParameters() as $parameter ){
			$signature	= $this->getParameterMarkUp( $parameter );
			$text		= $parameter->getDescription() ? '&nbsp;&minus;&nbsp;'.$parameter->getDescription() : "";
			$params[]	= $signature.$text;
		}
		$params	= implode( "<br/>", $params );
		$attributes['param']	= $this->buildParamList( $params, 'param' );

		$attributes	= $this->loadTemplate( 'interface.method.attributes', $attributes );

		$uri		= 'interface.'.$interface->getId().".html#source_interface_method_".$method->getName();
		$return		= $method->getReturn() ? $this->getTypeMarkUp( $method->getReturn()->getType() ) : "";
		$methodLink	= HtmlElements::Link( $uri, $method->getName() );

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
		return $this->loadTemplate( 'interface.method', $data );
	}

	/**
	 *	Builds View of Interface Methods for Interface Information File.
	 *	@access		public
	 *	@param		PhpInterface		$interface			Interface Object
	 *	@return		string
	 */
	public function buildView( PhpInterface $interface ): string
	{
		$this->type	= "interface";

		$list		= array();
		$methods	= $interface->getMethods();
		if( !$methods )
			return '';
		ksort( $methods );
		foreach( $methods as $methodName => $methodData )
			$list[$methodName]	= $this->buildMethodEntry( $interface, $methodData );

		$words		= $this->env->words['interfaceMethods'];
		$heading	= sprintf( $words['heading'], $interface->getName() );
		$data	= array(
			'words'		=> $words,
			'heading'	=> $heading,
			'list'		=> implode( "", $list ),
			'inherited'	=> $this->buildInheritedMethodList( $interface, array_keys( $list ) ),
		);
		return $this->loadTemplate( 'interface.methods', $data );
	}
}

