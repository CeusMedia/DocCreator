<?php
/**
 *	Builds Interface Information View.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interface
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Info.php5 82 2011-10-03 00:45:13Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\Interfaces;
/**
 *	Builds Interface Information View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interface
 *	@extends		DocCreator_Builder_HTML_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Info.php5 82 2011-10-03 00:45:13Z christian.wuerker $
 *	@todo			Code Doc
 */
class Info extends \CeusMedia\DocCreator\Builder\HTML\Abstraction{

	protected function buildParamArtefactList( $parent, $value, $key, $list = array() ){
		$list	= array();
		if( is_string( $value ) )
			return $this->buildParamList( $value, $key );

		if( is_array( $value ) ){
			foreach( $value as $artefact )
				if( $artefact !== $parent )
					$list[]	= \UI_HTML_Elements::ListItem( $this->getTypeMarkUp( $artefact ), 0, array( 'class' => 'class' ) );
		}
		else if( $value )
			$list[]	= \UI_HTML_Elements::ListItem( $this->getTypeMarkUp( $value ), 0, array( 'class' => 'class' ) );

		return $this->buildParamList( $list, $key );
	}

	protected function buildParamClassList( $parent, $value, $key, $list = array() ){
		return $this->buildParamArtefactList( $parent, $value, $key, $list );
	}

	protected function buildParamInterfaceList( $parent, $value, $key, $list = array() ){
		return $this->buildParamArtefactList( $parent, $value, $key, $list );
	}

	/**
	 *	Builds List of License Attributes.
	 *	@access		protected
	 *	@param		array			$data		Array of File Data
	 *	@param		array			$list		List to fill
	 *	@return		array
	 */
	protected function buildParamLicenses( $data, $list = array() ){
		if( !$data->getLicenses() )
			return "";
		foreach( $data->getLicenses() as $license ){
			$label	= $license->getName();
			if( $license->getUrl() ){
				$url	= $license->getUrl().'?KeepThis=true&TB_iframe=true';
				$class	= 'file-info-license';
				$label	= \UI_HTML_Elements::Link( $url, $label, $class );
			}
			$list[]	= $this->loadTemplate( 'file.info.param.item', array( 'value' => $label ) );
		}
		return $this->buildParamList( $list, 'licenses' );
	}

	/**
	 *	Builds Return Description.
	 *	@access		protected
	 *	@param		ADT_PHP_Function	$data		Data object of function or method
	 *	@return		string				Return Description
	 */
	protected function buildParamReturn( \ADT_PHP_Function $data ){
		if( !$data->getReturn() )
			return "";
		$type	= $data->getReturn()->getType() ? $this->getTypeMarkUp( $data->getReturn()->getType() ) : "";
		if( $data->getReturn()->getDescription() )
			$type	.= " ".$data->getReturn()->getDescription();
		return $this->buildParamList( $type, 'return' );
	}

	/**
	 *	Builds Authors Entry for Parameters List.
	 *	@access		protected
	 *	@param		array			$data		Authors Data Array
	 *	@param		array			$list		List to fill
	 *	@return		string
	 */
	protected function buildParamThrows( $data, $list = array() ){
		foreach( $data->getThrows() as $throws ){
			$type	= $throws->getName() ? $this->getTypeMarkUp( $throws->getName() ) : "";
			$type	.= $throws->getReason() ? " ".$throws->getReason() : "";
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $type ) );
		}
		return $this->buildParamList( $list, 'throws' );
	}

	private function buildRelationTree( \ADT_PHP_Interface $interface ){
		$interfaces = $this->getSuperInterfaces( $interface );
		if( !$interfaces )
			return;
		array_unshift( $interfaces, $interface );
		$tree	= "";
		foreach( $interfaces as $interfaceName ){
			$interfaceName	= $this->getTypeMarkUp( $interfaceName ).$tree;
			$item	= \UI_HTML_Elements::ListItem( $interfaceName, 0, array( 'class' => 'class' ) );
			$tree	= \UI_HTML_Elements::unorderedList( array( $item ) );
		}
		return $this->buildParamList( $tree, 'inheritance' );
	}

	public function buildView( \ADT_PHP_Interface $interface ){
		$this->type		= 'interface';

		$package		= $this->buildPackageLink( $interface->getPackage(), $interface->getCategory() );
		$category		= $this->buildCategoryLink( $interface->getCategory() );

		$attributeData	= array(
			'description'	=> $this->getFormatedDescription( $interface->getDescription() ),
			'category'		=> $this->buildParamStringList( $category, 'category' ),						//  category (linked if resolvable)
			'package'		=> $this->buildParamStringList( $package, 'package' ),							//  package (linked if resolvable)
			'version'		=> $this->buildParamStringList( $interface->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $interface->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $interface->getCopyright(), 'copyright' ),		//  copyright notes
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
		$uiData	= array(
			'attributes'	=> $attributes,
			'relations'		=> $relations,
		);
		if( !max( $uiData ) )
			return "";
		$uiData['words']	= $this->env->words['interfaceInfo'];
		return $this->loadTemplate( 'interface.info', $uiData );
	}

	/**
	 *	Returns a list of backwards resolved superinterfaces, either as object or string if unresolvable.
	 *	@access		protected
	 *	@param		ADT_PHP_Interface	$interface		Interface to get list of superinterfaces for
	 *	@return		array				List of superinterfaces
	 */
	protected function getSuperInterfaces( \ADT_PHP_Interface $interface ){
		$list	= array();																			//  prepare empty list
		while( $superInterface = $interface->getExtendedInterface() ){								//  while internal interface has superinterface
			$list[]		= $superInterface;															//  set reference to found superinterface
			if( !is_object( $superInterface ) )														//  found superinterface is not resolvable
				break;																				//  break recursion
			$interface	= $superInterface;															//  set internal interface for recursion
		}
		return $list;																				//  return list of superinterfaces
	}
}
?>
