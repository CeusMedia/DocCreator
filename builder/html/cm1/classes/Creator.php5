<?php
/**
 *	Creates Documentation Files from Parser Data.
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
 *	@package		DocCreator_Builder_HTML_CM1
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Creator.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
import( 'de.ceus-media.ui.html.Elements' );
import( 'de.ceus-media.folder.RecursiveLister' );
import( 'de.ceus-media.folder.RecursiveIterator' );
import( 'de.ceus-media.alg.time.Clock' );
import( 'de.ceus-media.alg.StringTrimmer' );
import( 'core.Environment' );
import( 'builder.html.cm1.classes.site.Control' );
import( 'builder.html.cm1.classes.site.Category' );
import( 'builder.html.cm1.classes.site.Package' );
import( 'builder.html.cm1.classes.class.Builder' );
import( 'builder.html.cm1.classes.file.Builder' );
import( 'builder.html.cm1.classes.site.Builder' );
/**
 *	Creates Documentation Files from Parser Data.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1
 *	@uses			Folder_RecursiveIterator
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Clock
 *	@uses			Alg_StringTrimmer
 *	@uses			DocCreator_Core_Environment
 *	@uses			Builder_HTML_CM1_Site_Control
 *	@uses			Builder_HTML_CM1_Site_Package
 *	@uses			Builder_HTML_CM1_Class_Builder
 *	@uses			Builder_HTML_CM1_File_Builder
 *	@uses			Builder_HTML_CM1_Site_Builder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Creator.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
class Builder_HTML_CM1_Creator
{
	private $linkList			= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Configuration	$config			Configuration Array Object 
	 *	@param		bool							$options		Flag: be verbose
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Environment $env, XML_Element $builder, $verbose = NULL )
	{
		$options		= array( 'creator.verbose' => $verbose );
		$this->env		= $env;
		$this->config	= $this->env->config;
		$this->env->openBuilder( $builder );
		$this->env->load();


		$this->siteBuilder	= new Builder_HTML_CM1_Site_Builder( $this->env );

		$this->pathBuilder	= dirname( dirname( __FILE__ ) )."/";
		$this->pathTarget	= $this->env->getBuilderTargetPath();
		if( !file_exists( $this->pathTarget ) )
			mkDir( $this->pathTarget, 0775, TRUE );

		if( $this->env->config->getSkip( 'creator' ) )
		{
			if( $this->env->config->getVerbose( 'skip' ) )
				remark( "Skipping creation of files about classes, files, packages, categories" );
		}
		else
		{
			$this->createFiles();
			$this->createPackages();
			$this->createCategories();
		}

		if( $this->env->config->getSkip( 'info' ) )
		{
			if( $this->env->config->getVerbose( 'skip' ) )
				remark( "Skipping info sites, frameset and control (links, tree)" );
		}
		else
		{
			$this->createSites();
			$this->createControl();
		}

		if( $this->env->config->getSkip( 'resources' ) )
		{
			if( $this->env->config->getVerbose( 'skip' ) )
				remark( "Skipping resources (images, icons, stylesheets, javascripts)" );
		}				
		else
		{
			$pathTheme	= 'themes/'.$this->env->getBuilderTheme().'/';
			$this->copyResourcesRecursive( $pathTheme.'js/', 'js/', "JavaScripts" );
			$this->copyResourcesRecursive( $pathTheme.'css/', 'css/', "Stylesheets" );
			$this->copyResourcesRecursive( $pathTheme.'images/', "images/", "Images" );
		}
	}
	
	protected function copyResourcesRecursive( $pathSource, $pathTarget, $label )
	{
		$pathSource	= $this->pathBuilder.$pathSource;
		$pathTarget	= $this->pathTarget.$pathTarget;
		if( !file_exists( $pathTarget ) )
			mkDir( $pathTarget, 0775, TRUE );

#		$index	= new Folder_RecursiveLister( $pathSource );
		$index	= new Folder_RecursiveIterator( $pathSource );
		$length	= strlen( $pathSource );
		if( $this->env->verbose )
			remark( "Copying: ".$label );
		foreach( $index as $entry )
		{
			$name	= substr( $entry->getPathname(), $length );
			if( $entry->isFile() )
			{
				if( preg_match( "@\.skip@i", $entry->getPathname() ) )
					continue;
#				if( $this->env->verbose )
#					remark( "Copying: ".Alg_StringTrimmer::trimCentric( $pathTarget.$name, 70 ) );
				if( !@copy( $entry->getPathname(), $pathTarget.$name ) )
					throw new RuntimeException( 'File "'.$entry->getPathname().'" could not be copied to "'.$pathTarget.$name.'"' ); 
			}
			else if( $entry->isDir() && !file_exists( $pathTarget.$name ) )
				mkDir( $pathTarget.$name );
		}	
	}
	
	protected function createCategories( $prefix = "category." )
	{

		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new Builder_HTML_CM1_Site_Category( $this->env );
		foreach( $this->env->tree->getPackages() as $category )
		{
			$categoryId	= $category->getId();
			$fileName	= $prefix.$categoryId.".html";
			$view		= $builder->buildView( $category );
			if( $this->env->verbose )
				remark( "Writing Category: ".Alg_StringTrimmer::trimCentric( $categoryId, 60 ) );
			file_put_contents( $pathTarget.$fileName, $view );
		}
	}
	
	protected function createControl()
	{
		$builder	= new Builder_HTML_CM1_Site_Control( $this->env );
		$builder->createControl( $this->linkList );
	}

	protected function createFiles( $prefix = "class." )
	{
		$clock		= new Alg_Time_Clock;
		$pathTarget	= $this->pathTarget.$prefix;
#		if( !file_exists( $pathTarget ) )
#			mkDir( $pathTarget, 0775, TRUE );

		$fileBuilder	= new Builder_HTML_CM1_File_Builder( $this->env );
		$classBuilder	= new Builder_HTML_CM1_Class_Builder( $this->env, $fileBuilder );
		foreach( $this->env->data->getFiles() as $fileName => $file )
		{
			$clock2		= new Alg_Time_Clock;
			if( $file->hasClasses() )
			{
				foreach( $file->getClasses() as $class )
				{
					$classId	= $class->getId();
					$docFile	= $pathTarget.$classId.".html";
					if( $this->env->verbose )
						remark( "Creating: ".Alg_StringTrimmer::trimCentric( $classId, 68 )  );
					$view		= $classBuilder->buildView( $file );
					file_put_contents( $docFile, $view );
				}
			}
/*			else
			{
				$view		= $fileBuilder->buildView( $file );
				file_put_contents( $docFile, $view );
			}*/
			$file->timeBuild	= $clock2->stop( 6, 0 );
		}
		$this->env->timeBuild	= $clock->stop( 6, 0 );
	}
	
	protected function createPackages( $prefix = "package." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new Builder_HTML_CM1_Site_Category( $this->env );
		foreach( $this->env->tree->getPackages() as $category )
			$this->createPackageRecursive( $category, $prefix );
	}
	
	private function createPackageRecursive( ADT_PHP_Category $superPackage, $prefix = "package." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new Builder_HTML_CM1_Site_Package( $this->env );
		foreach( $superPackage->getPackages() as $package )
		{
			$packageId	= $package->getId();
			$fileName	= $prefix.$packageId.".html";
			$view		= $builder->buildView( $package );
			if( $this->env->verbose )
				remark( "Writing Package: ".Alg_StringTrimmer::trimCentric( $packageId, 61 ) );
			file_put_contents( $pathTarget.$fileName, $view );
			$this->createPackageRecursive( $package, $prefix );
		}
	}
	
	private function createSites()
	{
		$builder	= new Builder_HTML_CM1_Site_Builder( $this->env );
		$builder->createSites( $this->linkList );
	}
}
?>