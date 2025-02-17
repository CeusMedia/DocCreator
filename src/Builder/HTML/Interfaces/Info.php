<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Interface Information View.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interfaces
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Interfaces;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;
use CeusMedia\PhpParser\Structure\Function_ as PhpFunction;
use CeusMedia\PhpParser\Structure\Method_ as PhpMethod;
use CeusMedia\PhpParser\Structure\Trait_ as PhpTrait;

/**
 *	Builds Interface Information View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interfaces
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@todo			Code Doc
 */
class Info extends HtmlBuilderAbstraction
{
	public function buildView( object $interface ): string
	{
		$this->type		= 'interface';

		$package		= $this->buildPackageLink( $interface->getPackage(), $interface->getCategory() );
		$category		= $this->buildCategoryLink( $interface->getCategory() );

		$attributeData	= array(
			'description'	=> $this->getFormatedDescription( $interface->getDescription() ),
			'category'		=> $this->buildParamStringList( $category, 'category' ),						//  category (linked if resolvable)
			'package'		=> $this->buildParamStringList( $package, 'package' ),							//  package (linked if resolvable)
			'version'		=> $this->buildParamStringList( $interface->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $interface->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $interface->getCopyrights(), 'copyright' ),		//  copyright notes
			'authors'		=> $this->buildParamAuthors( $interface ),										//  authors
			'link'			=> $this->buildParamLinkedList( $interface->getLinks(), 'link' ),				//  links
			'see'			=> $this->buildParamLinkedList( $interface->getSees(), 'see' ),					//  see-also-references
			'license'		=> $this->buildParamLicenses( $interface ),										//  licenses
			'deprecated'	=> $this->buildParamStringList( $interface->getDeprecations(), 'deprecated' ),	//  deprecation notes
			'todo'			=> $this->buildParamStringList( $interface->getTodos(), 'todo' ),				//  todo notes
		);
		$relationData	= array(
			'tree'			=> $this->buildRelationTree( $interface ),
			'extends'		=> $this->buildParamInterfaceList( $interface, $interface->getExtendedInterface(), 'extends' ),			//  uses
			'extendedBy'	=> $this->buildParamInterfaceList( $interface, $interface->getExtendingInterfaces(), 'extendedBy' ),	//  extended by classes
			'implementedBy'	=> $this->buildParamClassList( $interface, $interface->getImplementingClasses(), 'implementedBy' ),	//  implemented by classes
#			'usedBy'		=> $this->buildParamClassList( $interface, $interface->getUsingClasses(), 'usedBy' ),				//  used by classes
			'composedBy'	=> $this->buildParamClassList( $interface, $interface->getComposingClasses(), 'composedBy' ),		//  extended by class
			'receivedBy'	=> $this->buildParamClassList( $interface, $interface->getReceivingClasses(), 'receivedBy' ),		//  received by classes
			'returnedBy'	=> $this->buildParamClassList( $interface, $interface->getReturningClasses(), 'returnedBy' ),		//  return by classes
		);
		$attributes	= max( $attributeData ) ? $this->loadTemplate( 'interface.info.attributes', $attributeData ) : "";
		$relations	= max( $relationData ) ? $this->loadTemplate( 'interface.info.relations', $relationData ) : "";
		$uiData	= [
			'attributes'	=> $attributes,
			'relations'		=> $relations,
		];
		if( !max( $uiData ) )
			return "";
		$uiData['words']	= $this->env->words['interfaceInfo'];
		return $this->loadTemplate( 'interface.info', $uiData );
	}

	protected function buildParamInterfaceList( $parent, $value, $key, array $list = [] ): string
	{
		return $this->buildParamArtefactList( $parent, $value, $key, $list );
	}

	private function buildRelationTree( PhpInterface|PhpClass|PhpTrait $interface ): string
	{
		$interfaces = $this->getSuperInterfaces( $interface );
		if( 0 === count( $interfaces ) )
			return '';
		array_unshift( $interfaces, $interface );
		$tree	= '';
		foreach( $interfaces as $interfaceName ){
			$interfaceName	= $this->getTypeMarkUp( $interfaceName ).$tree;
			$item	= HtmlElements::ListItem( $interfaceName, 0, ['class' => 'class'] );
			$tree	= HtmlElements::unorderedList( [$item] );
		}
		return $this->buildParamList( $tree, 'inheritance' );
	}

	/**
	 *	Returns a list of backwards resolved superinterfaces, either as object or string if unresolvable.
	 *	@access		protected
	 *	@param		PhpInterface	$interface		Interface to get list of superinterfaces for
	 *	@return		array				List of superinterfaces
	 */
	protected function getSuperInterfaces( PhpInterface $interface ): array
	{
		$list	= [];																			//  prepare empty list
		while( $superInterface = $interface->getExtendedInterface() ){								//  while internal interface has superinterface
			$list[]		= $superInterface;															//  set reference to found superinterface
			if( !is_object( $superInterface ) )														//  found superinterface is not resolvable
				break;																				//  break recursion
			$interface	= $superInterface;															//  set internal interface for recursion
		}
		return $list;																				//  return list of superinterfaces
	}
}
