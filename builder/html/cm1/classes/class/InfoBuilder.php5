<?php
/**
 *	Builds Class Information View.
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
 *	@version		$Id: InfoBuilder.php5 731 2009-10-21 06:11:05Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.file.InfoBuilder' );
/**
 *	Builds Class Information View.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Class
 *	@extends		Builder_HTML_CM1_File_InfoBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: InfoBuilder.php5 731 2009-10-21 06:11:05Z christian.wuerker $
 *	@todo			Code Doc
 */
class Builder_HTML_CM1_Class_InfoBuilder extends Builder_HTML_CM1_File_InfoBuilder
{
	protected function buildParamClassList( $parent, $value, $key, $list = array() )
	{
		$list	= array();
		if( is_string( $value ) )
			return $this->buildParamList( $value, $key );

		if( is_array( $value ) )
		{
			foreach( $value as $class )
				if( $class !== $parent )
					$list[]	= $this->loadTemplate( 'class.info.param.item', array( 'value' => $this->getTypeMarkUp( $class ) ) );
		}
		else if( $value )
			$list[]	= $this->loadTemplate( 'class.info.param.item', array( 'value' => $this->getTypeMarkUp( $value ) ) );

#		//  or in short
#		$array	= is_array( $data->$key ) ? $data->$key : array( $data->$key );
#			foreach( $data->$key as $classId )
#				$list[]	= $this->loadTemplate( 'class.info.param.item', array( 'value' => $this->getTypeMarkUp( $classId ) ) );

		return $this->buildParamList( $list, $key );
	}

	private function buildRelationTree( ADT_PHP_Class $class )
	{
		$classes = $this->getSuperClasses( $class );

		if( !$classes )
			return;
		array_unshift( $classes, $class->getName() );
		foreach( $classes as $className )
		{
			if( isset( $tree ) )
				$className	= $this->getTypeMarkUp( $className ).$tree;
			$item	= UI_HTML_Elements::ListItem( $className );
			$tree	= UI_HTML_Elements::unorderedList( array( $item ) );
		}
		return $this->buildParamList( $tree, 'inheritance' );
	}
	
	public function buildView( ADT_PHP_Class $class )
	{
		$this->type		= 'class';

		$attributeData	= array(
			'description'	=> nl2br( trim( (string) $class->getDescription() ) ),
			'category'		=> $this->buildParamStringList( $class->getCategory(), 'category' ),		//  category
			'package'		=> $this->buildParamStringList( $class->getPackage(), 'package' ),			//  package
			'version'		=> $this->buildParamStringList( $class->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $class->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $class->getCopyright(), 'copyright' ),		//  copyright notes
			'authors'		=> $this->buildParamAuthors( $class ),										//  authors
			'link'			=> $this->buildParamLinkedList( $class->getLinks(), 'link' ),				//  links
			'see'			=> $this->buildParamLinkedList( $class->getSees(), 'see' ),					//  see-also-references
			'license'		=> $this->buildParamLicenses( $class ),										//  licenses
			'deprecated'	=> $this->buildParamStringList( $class->getDeprecations(), 'deprecated' ),	//  deprecation notes
			'todo'			=> $this->buildParamStringList( $class->getTodos(), 'todo' ),				//  todo notes
			'abstract'		=> $this->buildParamList( $class->isAbstract() ? " " : "", 'abstract' ),
			'final'			=> $this->buildParamList( $class->isFinal() ? " " : "", 'final' ),
		);
		$relationData	= array(
			'tree'			=> $this->buildRelationTree( $class ),
			'extends'		=> $this->buildParamClassList( $class, $class->getExtendedClass(), 'extends' ),							//  uses
			'uses'			=> $this->buildParamClassList( $class, $class->getUsedClasses(), 'uses' ),							//  uses
			'implements'	=> $this->buildParamClassList( $class, $class->getImplementedInterfaces(), 'implements' ),						//  implements
			'extendedBy'	=> $this->buildParamClassList( $class, $class->getExtendingClasses(), 'extendedBy' ),		//  extended by classes
			'implementedBy'	=> $this->buildParamClassList( $class, $class->getImplementingClasses(), 'implementedBy' ),	//  implemented by classes
			'usedBy'		=> $this->buildParamClassList( $class, $class->getUsingClasses(), 'usedBy' ),				//  used by classes
			'composedBy'	=> $this->buildParamClassList( $class, $class->getComposingClasses(), 'composedBy' ),		//  extended by class
			'receivedBy'	=> $this->buildParamClassList( $class, $class->getReceivingClasses(), 'receivedBy' ),		//  received by classes
			'returnedBy'	=> $this->buildParamClassList( $class, $class->getReturningClasses(), 'returnedBy' ),		//  return by classes
		);
		$attributes	= max( $attributeData ) ? $this->loadTemplate( 'class.info.attributes', $attributeData ) : "";
		$relations	= max( $relationData ) ? $this->loadTemplate( 'class.info.relations', $relationData ) : "";
		$uiData	= array(
			'attributes'	=> $attributes,
			'relations'		=> $relations,
		);
		if( !max( $uiData ) )
			return "";
		$uiData['words']	= $this->env->words['classInfo'];
		return $this->loadTemplate( 'class.info', $uiData );
	}

	/**
	 *	Returns a list of backwards resolved superclasses, either as object or string if unresolvable.
	 *	@access		protected
	 *	@param		ADT_PHP_Class		$class		Class to get list of superclasses for
	 *	@return		array			List of superclasses
	 */
	protected function getSuperClasses( ADT_PHP_Class $class )
	{
		$list	= array();																			//  prepare empty list
		while( $superClass = $class->getExtendedClass() )											//  while internal class has superclass
		{
			$list[]	= $superClass;																	//  set reference to found superclass
			$class	= $superClass;																	//  set internal class for recursion
			if( !( $superClass instanceof ADT_PHP_Class ) )											//  found superclass is not resolvable
				break;																				//  break recursion
		}
		return $list;																				//  return list of superclasses
	}
}
?>