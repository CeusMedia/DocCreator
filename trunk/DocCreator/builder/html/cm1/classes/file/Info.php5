<?php
/**
 *	Builder for File Info View.
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
 *	@package		DocCreator_Builder_HTML_CM1_File
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: InfoBuilder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Abstract' );
/**
 *	Builder for File Info View.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_File
 *	@extends		Builder_HTML_CM1_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: InfoBuilder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
class Builder_HTML_CM1_File_Info extends Builder_HTML_CM1_Abstract
{
	/**
	 *	Builds List of License Attributes.
	 *	@access		protected
	 *	@param		array			$data		Array of File Data
	 *	@param		array			$list		List to fill
	 *	@return		array
	 */
	protected function buildParamLicenses( $data, $list = array() )
	{
		if( !$data->getLicenses() )
			return "";
		foreach( $data->getLicenses() as $license )
		{
			$label	= $license->getName();
			if( $license->getUrl() )
			{
				$url	= $license->getUrl().'?KeepThis=true&TB_iframe=true';
				$class	= 'file-info-license';
				$label	= UI_HTML_Elements::Link( $url, $label, $class );
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
	protected function buildParamReturn( ADT_PHP_Function $data )
	{
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
	protected function buildParamThrows( $data, $list = array() )
	{
		foreach( $data->getThrows() as $throws )
		{
			$type	= $throws->getName() ? $this->getTypeMarkUp( $throws->getName() ) : "";
			$type	.= $throws->getReason() ? " ".$throws->getReason() : "";
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $type ) );
		}
		return $this->buildParamList( $list, 'throws' );
	}

	/**
	 *	Builds File Info View.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file		File Object to build Info View for
	 *	@return		string
	 */
	public function buildView( ADT_PHP_File $file )
	{
		$this->type	= 'file';

		$attributesData	= array(
			'category'		=> $this->buildParamStringList( $file->getCategory(), 'category' ),			//  category
			'package'		=> $this->buildParamStringList( $file->getPackage(), 'package' ),			//  package
			'version'		=> $this->buildParamStringList( $file->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $file->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $file->getCopyright(), 'copyright' ),		//  copyright notes
			'authors'		=> $this->buildParamAuthors( $file ),										//  authors
			'link'			=> $this->buildParamLinkedList( $file->getLinks(), 'link' ),				//  links
			'see'			=> $this->buildParamLinkedList( $file->getSees(), 'see' ),					//  see-also-references
			'license'		=> $this->buildParamLicenses( $file ),										//  license notes
			'deprecated'	=> $this->buildParamStringList( $file->getDeprecations(), 'deprecated' ),	//  deprecated
			'todo'			=> $this->buildParamStringList( $file->getTodos(), 'todo' ),				//  todos
		);
		$attributes	= max( $attributesData ) ? $this->loadTemplate( 'file.info.attributes', $attributesData ) : "";
		$uiData	= array(
			'description'	=> nl2br( trim( (string) $file->getDescription() ) ),
			'attributes'	=> $attributes,
		);
		if( !max( $uiData ) )
			return "";
		$uiData['heading']	= $this->words['fileInfo']['heading'];
		return $this->loadTemplate( 'file.info', $uiData );
	}
}
?>