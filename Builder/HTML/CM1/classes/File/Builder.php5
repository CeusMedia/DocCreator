<?php
/**
 *	Builds File Information File.
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
 *	@version		$Id$
 */
/**
 *	Builds File Information File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_File
 *	@extends		Builder_HTML_CM1_Abstract
 *	@uses			Builder_HTML_CM1_File_Info
 *	@uses			Builder_HTML_CM1_File_Functions
 *	@uses			Builder_HTML_CM1_File_SourceCode
 *	@uses			Builder_HTML_CM1_File_Index
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
class Builder_HTML_CM1_File_Builder extends Builder_HTML_CM1_Abstract
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment Object
	 *	@return		void
	 */
	public function __construct( $env )
	{
		parent::__construct( $env );
		$this->builderFileInfo		= new Builder_HTML_CM1_File_Info( $env );
		$this->builderFileFunctions	= new Builder_HTML_CM1_File_Functions( $env );
		$this->builderSourceCode	= new Builder_HTML_CM1_File_SourceCode( $env );
		$this->builderIndex			= new Builder_HTML_CM1_File_Index( $env );
	}

	/**
	 *	Builds File View.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file			File Object
	 *	@return		string
	 */
	public function buildView( ADT_PHP_File $file )
	{
		$contents	= array();
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
	public function getFunctionList( ADT_PHP_File $file )
	{
		$list		= array();
		$functions	= $file->getFunctions();
		ksort( $functions );
		foreach( $functions as $function )
			$list[$function->getName()]	= $function->getDescription();
		return $list;
	}
}
?>