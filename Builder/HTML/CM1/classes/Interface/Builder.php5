<?php
/**
 *	Builds Interface Information File.
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
 *	@package		DocCreator_Builder_HTML_CM1_Interface
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Abstract' );
import( 'builder.html.cm1.classes.interface.Info' );
import( 'builder.html.cm1.classes.interface.Methods' );
import( 'builder.html.cm1.classes.file.Info' );
import( 'builder.html.cm1.classes.file.Functions' );
import( 'builder.html.cm1.classes.file.SourceCode' );
import( 'builder.html.cm1.classes.file.Index' );
/**
 *	Builds Interface Information File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Interface
 *	@extends		Builder_HTML_CM1_Abstract
 *	@uses			Builder_HTML_CM1_Interface_Info
 *	@uses			Builder_HTML_CM1_Interface_Methods
 *	@uses			Builder_HTML_CM1_File_Info
 *	@uses			Builder_HTML_CM1_File_Functions
 *	@uses			Builder_HTML_CM1_File_SourceCode
 *	@uses			Builder_HTML_CM1_File_Index
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
class Builder_HTML_CM1_Interface_Builder extends Builder_HTML_CM1_Abstract
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Environment	$env	Environment Object
	 *	@return		void
	 */
	public function __construct( $env )
	{
		parent::__construct( $env );
		$this->builderFile			= new Builder_HTML_CM1_File_Info( $env );
		$this->builderFunctions		= new Builder_HTML_CM1_File_Functions( $env );
		$this->builderInterface		= new Builder_HTML_CM1_Interface_Info( $env );
		$this->builderMethods		= new Builder_HTML_CM1_Interface_Methods( $env );
		$this->builderSourceCode	= new Builder_HTML_CM1_File_SourceCode( $env );
		$this->builderIndex			= new Builder_HTML_CM1_File_Index( $env );
	}

	/**
	 *	Builds entire Interface Information File.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file			File Data Object
	 *	@param		ADT_PHP_Interface	$interface		Interface Data Object
	 *	@return		string
	 */
	public function buildView( ADT_PHP_File $file, ADT_PHP_Interface $interface )
	{
		$contents	= array();
		$data	= array(
			'words'				=> $this->env->words['interfaceInfo'],
			'index'				=> $this->builderIndex->buildIndex( $file ),
			'interfaceName'		=> $interface->getName(),
			'fileName'			=> $file->getBasename(),
			'pathName'			=> $file->getPathname(),
			'fileInfo'			=> $this->builderFile->buildView( $file ),
			'interfaceInfo'		=> $this->builderInterface->buildView( $interface ),
			'interfaceMethods'	=> $this->builderMethods->buildView( $interface ),
			'fileFunctions'		=> $this->builderFunctions->buildView( $file ),
			'fileSource'		=> $this->builderSourceCode->buildSourceCode( $file, TRUE ),
			'footer'			=> $this->buildFooter(),
		);
		return $this->loadTemplate( 'interface.content', $data );
	}
}
?>