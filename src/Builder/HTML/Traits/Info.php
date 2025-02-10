<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Trait Information View.
 *
 *	Copyright (c) 2008-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2025 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Traits;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Interfaces\Info as InfoInterface;
use CeusMedia\PhpParser\Structure\Trait_ as PhpTrait;

/**
 *	Builds Trait Information View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Classes
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2025 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@todo			Code Doc
 */
class Info extends InfoInterface
{
	/**
	 *	@param		PhpTrait		$trait
	 *	@return		string
	 */
	public function buildView( object $trait ): string
	{
		/** @var PhpTrait $trait */
		$this->type		= 'class';

		$package		= $this->buildPackageLink( $trait->getPackage(), $trait->getCategory() );
		$category		= $this->buildCategoryLink( $trait->getCategory() );
		$package		= $this->getTypeMarkUp( $package );
		$category		= $this->getTypeMarkUp( $category );

		$attributeData	= [
			'description'	=> $this->getFormatedDescription( $trait->getDescription() ),
			'category'		=> $this->buildParamStringList( $category, 'category' ),					//  category (linked if resolvable)
			'package'		=> $this->buildParamStringList( $package, 'package' ),						//  package (linked if resolvable)
			'version'		=> $this->buildParamStringList( $trait->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $trait->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $trait->getCopyrights(), 'copyright' ),		//  copyright notes
			'authors'		=> $this->buildParamAuthors( $trait ),										//  authors
			'link'			=> $this->buildParamLinkedList( $trait->getLinks(), 'link' ),				//  links
			'see'			=> $this->buildParamLinkedList( $trait->getSees(), 'see' ),					//  see-also-references
			'license'		=> $this->buildParamLicenses( $trait ),										//  licenses
			'deprecated'	=> $this->buildParamStringList( $trait->getDeprecations(), 'deprecated' ),	//  deprecation notes
			'todo'			=> $this->buildParamStringList( $trait->getTodos(), 'todo' ),				//  todo notes
		];
		$relationData	= [
			'tree'			=> $this->buildRelationTree( $trait ),
			'uses'			=> $this->buildParamClassList( $trait, $trait->getUsingTraits(), 'uses' ),					//  uses
			'usedBy'		=> $this->buildParamClassList( $trait, $trait->getUsingClasses(), 'usedBy' ),				//  used by classes
			'composedBy'	=> '',//$this->buildParamClassList( $trait, $trait->getComposingClasses(), 'composedBy' ),		//  extended by class
			'receivedBy'	=> '',//$this->buildParamClassList( $trait, $trait->getReceivingClasses(), 'receivedBy' ),		//  received by classes
			'returnedBy'	=> '',//$this->buildParamClassList( $trait, $trait->getReturningClasses(), 'returnedBy' ),		//  return by classes
		];
		$attributes	= max( $attributeData ) ? $this->loadTemplate( 'trait.info.attributes', $attributeData ) : "";
		$relations	= max( $relationData ) ? $this->loadTemplate( 'trait.info.relations', $relationData ) : "";
		$uiData	= [
			'attributes'	=> $attributes,
			'relations'		=> $relations,
		];
		if( !max( $uiData ) )
			return '';
		$uiData['words']	= $this->env->words['classInfo'];
		return $this->loadTemplate( 'trait.info', $uiData );
	}

	/**
	 *	Returns a list of backwards resolved superclasses, either as object or string if unresolvable.
	 *	@access		protected
	 *	@param		PhpTrait	$trait		Class to get list of superclasses for
	 *	@return		array		List of superclasses
	 */
	protected function getSuperClasses( PhpTrait $trait ): array
	{
		$list	= [];																			//  prepare empty list
		while( $superClass = $trait->getUsingTraits() ){											//  while internal class has superclass
			$list[]	= $superClass;																	//  set reference to found superclass
			if( !is_object( $superClass ) )															//  found superclass is not resolvable
				break;																				//  break recursion
			$class	= $superClass;																	//  set internal class for recursion
			foreach( $this->getSuperClasses( $superClass ) as $item )
				$list[]	= $item;
		}
		return $list;																				//  return list of superclasses
	}

	private function buildRelationTree( PhpTrait $trait ): string
	{
		$traits	= $this->getSuperClasses( $trait );
		if( 0 === count( $traits ) )
			return '';
		array_unshift( $traits, $trait );
		$tree	= "";
		foreach( $traits as $traitName ){
			$traitName	= $this->getTypeMarkUp( $traitName ).$tree;
			$item	= HtmlElements::ListItem( $traitName, 0, ['class' => 'class'] );
			$tree	= HtmlElements::unorderedList( [$item] );
		}
		return $this->buildParamList( $tree, 'inheritance' );
	}
}
