<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Class Information View.
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

namespace CeusMedia\DocCreator\Builder\HTML\Classes;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Interfaces\Info as InfoInterface;
use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;

/**
 *	Builds Class Information View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Classes
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@todo			Code Doc
 */
class Info extends InfoInterface
{
	public function buildView( object $class ): string
	{
		$this->type		= 'class';

		$package		= $this->buildPackageLink( $class->getPackage(), $class->getCategory() );
		$category		= $this->buildCategoryLink( $class->getCategory() );
		$package		= $this->getTypeMarkUp( $package );
		$category		= $this->getTypeMarkUp( $category );

		$attributeData	= array(
			'description'	=> $this->getFormatedDescription( $class->getDescription() ),
			'category'		=> $this->buildParamStringList( $category, 'category' ),					//  category (linked if resolvable)
			'package'		=> $this->buildParamStringList( $package, 'package' ),						//  package (linked if resolvable)
			'version'		=> $this->buildParamStringList( $class->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $class->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $class->getCopyrights(), 'copyright' ),		//  copyright notes
			'authors'		=> $this->buildParamAuthors( $class ),										//  authors
			'link'			=> $this->buildParamLinkedList( $class->getLinks(), 'link' ),				//  links
			'see'			=> $this->buildParamLinkedList( $class->getSees(), 'see' ),					//  see-also-references
			'license'		=> $this->buildParamLicenses( $class ),										//  licenses
			'deprecated'	=> $this->buildParamStringList( $class->getDeprecations(), 'deprecated' ),	//  deprecation notes
			'todo'			=> $this->buildParamStringList( $class->getTodos(), 'todo' ),				//  todo notes
			'abstract'		=> $this->buildParamList( $class->isAbstract() ? " " : "", 'abstract' ),
		);
		if( $class instanceof PhpClass )
			$attributeData['final']	= $this->buildParamList( $class->isFinal() ? " " : "", 'final' );
		$relationData	= array(
			'tree'			=> $this->buildRelationTree( $class ),
			'extends'		=> $this->buildParamClassList( $class, $class->getExtendedClass(), 'extends' ),				//  uses
			'uses'			=> $this->buildParamClassList( $class, $class->getUsedClasses(), 'uses' ),					//  uses
			'implements'	=> $this->buildParamClassList( $class, $class->getImplementedInterfaces(), 'implements' ),	//  implements
			'extendedBy'	=> $this->buildParamClassList( $class, $class->getExtendingClasses(), 'extendedBy' ),		//  extended by classes
			'usedBy'		=> $this->buildParamClassList( $class, $class->getUsingClasses(), 'usedBy' ),				//  used by classes
			'composedBy'	=> $this->buildParamClassList( $class, $class->getComposingClasses(), 'composedBy' ),		//  extended by class
			'receivedBy'	=> $this->buildParamClassList( $class, $class->getReceivingClasses(), 'receivedBy' ),		//  received by classes
			'returnedBy'	=> $this->buildParamClassList( $class, $class->getReturningClasses(), 'returnedBy' ),		//  return by classes
		);
		$attributes	= max( $attributeData ) ? $this->loadTemplate( 'class.info.attributes', $attributeData ) : "";
		$relations	= max( $relationData ) ? $this->loadTemplate( 'class.info.relations', $relationData ) : "";
		$uiData	= [
			'attributes'	=> $attributes,
			'relations'		=> $relations,
		];
		if( !max( $uiData ) )
			return "";
		$uiData['words']	= $this->env->words['classInfo'];
		return $this->loadTemplate( 'class.info', $uiData );
	}

	/**
	 *	Returns a list of backwards resolved superclasses, either as object or string if unresolvable.
	 *	@access		protected
	 *	@param		PhpClass	$class		Class to get list of superclasses for
	 *	@return		array		List of superclasses
	 */
	protected function getSuperClasses( PhpClass $class ): array
	{
		$list	= [];																			//  prepare empty list
		while( $superClass = $class->getExtendedClass() ){											//  while internal class has superclass
			$list[]	= $superClass;																	//  set reference to found superclass
			if( !is_object( $superClass ) )															//  found superclass is not resolvable
				break;																				//  break recursion
			$class	= $superClass;																	//  set internal class for recursion
		}
		return $list;																				//  return list of superclasses
	}

	private function buildRelationTree( PhpClass $class ): string
	{
		$classes = $this->getSuperClasses( $class );
		if( 0 === count( $classes ) )
			return '';
		array_unshift( $classes, $class );
		$tree	= "";
		foreach( $classes as $className ){
			$className	= $this->getTypeMarkUp( $className ).$tree;
			$item	= HtmlElements::ListItem( $className, 0, ['class' => 'class'] );
			$tree	= HtmlElements::unorderedList( [$item] );
		}
		return $this->buildParamList( $tree, 'inheritance' );
	}
}
