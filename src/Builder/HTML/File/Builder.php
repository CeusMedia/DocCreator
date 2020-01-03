<?php
/**
 *	Builds File Information File.
 *
 *	Copyright (c) 2008-2020 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 82 2011-10-03 00:45:13Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\File;
/**
 *	Builds File Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@extends		DocCreator_Builder_HTML_Abstract
 *	@uses			DocCreator_Builder_HTML_File_Info
 *	@uses			DocCreator_Builder_HTML_File_Functions
 *	@uses			DocCreator_Builder_HTML_File_SourceCode
 *	@uses			DocCreator_Builder_HTML_File_Index
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 82 2011-10-03 00:45:13Z christian.wuerker $
 */
class Builder extends \CeusMedia\DocCreator\Builder\HTML\Abstraction{

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment Object
	 *	@return		void
	 */
	public function __construct( $env ){
		parent::__construct( $env );
		$this->builderFileInfo		= new \CeusMedia\DocCreator\Builder\HTML\File\Info( $env );
		$this->builderFileFunctions	= new \CeusMedia\DocCreator\Builder\HTML\File\Functions( $env );
		$this->builderSourceCode	= new \CeusMedia\DocCreator\Builder\HTML\File\SourceCode( $env );
		$this->builderIndex			= new \CeusMedia\DocCreator\Builder\HTML\File\Index( $env );
	}

	/**
	 *	Builds File View.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file			File Object
	 *	@return		string
	 */
	public function buildView( \ADT_PHP_File $file ){
		$data	= array(
			'fileName'		=> $file->getBasename(),
			'pathName'		=> $file->getPathname(),
			'index'			=> $this->builderIndex->buildIndex( $file ),
			'description'	=> $this->getFormatedDescription( $file->getDescription() ),
			'fileInfo'		=> $this->builderFileInfo->buildView( $file ),
			'fileFunctions'	=> $this->builderFileFunctions->buildView( $file ),
			'fileSource'	=> $this->builderSourceCode->buildSourceCode( $file ),
			'footer'		=> $this->buildFooter(),
		);
		return $this->loadTemplate( 'file.content', $data );
	}

	/**
	 *	Builds Function List of File.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file			File Object
	 *	@return		string
	 */
	public function getFunctionList( \ADT_PHP_File $file ){
		$list		= array();
		$functions	= $file->getFunctions();
		ksort( $functions );
		foreach( $functions as $function )
			$list[$function->getName()]	= $function->getDescription();
		return $list;
	}
}
?>
