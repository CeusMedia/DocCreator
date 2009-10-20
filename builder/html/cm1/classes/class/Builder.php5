<?php
/**
 *	Builds Class Information File.
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
 *	@version		$Id: Builder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Builder' );
import( 'builder.html.cm1.classes.class.InfoBuilder' );
import( 'builder.html.cm1.classes.class.MembersBuilder' );
import( 'builder.html.cm1.classes.class.MethodsBuilder' );
import( 'builder.html.cm1.classes.site.SourceCodeBuilder' );
import( 'builder.html.cm1.classes.site.IndexBuilder' );
import( 'builder.html.cm1.classes.file.Builder' );
import( 'builder.html.cm1.classes.file.InfoBuilder' );
import( 'builder.html.cm1.classes.file.FunctionsBuilder' );
/**
 *	Builds Class Information File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Class
 *	@extends		Builder_HTML_CM1_Builder
 *	@uses			Builder_HTML_CM1_Site_SourceCodeBuilder
 *	@uses			Builder_HTML_CM1_Site_IndexBuilder
 *	@uses			Builder_HTML_CM1_Class_InfoBuilder
 *	@uses			Builder_HTML_CM1_Class_MembersBuilder
 *	@uses			Builder_HTML_CM1_Class_MethodsBuilder
 *	@uses			Builder_HTML_CM1_File_Builder
 *	@uses			Builder_HTML_CM1_File_InfoBuilder
 *	@uses			Builder_HTML_CM1_File_FunctionsBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Builder_HTML_CM1_Class_Builder extends Builder_HTML_CM1_Builder
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment Object
	 *	@param		FileBuilder		$builderFile	File Builder Object
	 *	@return		void
	 */
	public function __construct( $env, $builderFile )
	{
		parent::__construct( $env );
		$this->builderIndex			= new Builder_HTML_CM1_Site_IndexBuilder( $env );
		$this->builderClassInfo		= new Builder_HTML_CM1_Class_InfoBuilder( $env );
		$this->builderClassMembers	= new Builder_HTML_CM1_Class_MembersBuilder( $env );
		$this->builderClassMethods	= new Builder_HTML_CM1_Class_MethodsBuilder( $env );
		$this->builderFileInfo		= new Builder_HTML_CM1_File_InfoBuilder( $env );
		$this->builderFileFunctions	= new Builder_HTML_CM1_File_FunctionsBuilder( $env );
		$this->builderSourceCode	= new Builder_HTML_CM1_Site_SourceCodeBuilder( $env );
		$this->builderFile			= $builderFile;
	}

	/**
	 *	Builds entire Class Information File.
	 *	@access		public
	 *	@param		Model_File		$file			File Object
	 *	@return		string
	 */
	public function buildView( $file )
	{
		$contents	= array();
		$functions	= $this->builderFile->getFunctionList( $file );
		$class		= array_shift( $file->getClasses() );
		$data	= array(
			'index'				=> $this->builderIndex->buildIndex( $file, $functions ),
			'className'			=> $class->getName(),
			'fileName'			=> $file->getBasename(),
			'pathName'			=> $file->getPathname(),
			'fileInfo'			=> $this->builderFileInfo->buildView( $file ),
			'classInfo'			=> $this->builderClassInfo->buildView( $class ),
			'classMembers'		=> $this->builderClassMembers->buildView( $class ),
			'classMethods'		=> $this->builderClassMethods->buildView( $class ),
			'fileFunctions'		=> $this->builderFileFunctions->buildView( $file ),
			'fileSource'		=> $this->builderSourceCode->buildSourceCode( $file, TRUE ),
		);
		return $this->loadTemplate( 'class.content', $data );
	}
}
?>