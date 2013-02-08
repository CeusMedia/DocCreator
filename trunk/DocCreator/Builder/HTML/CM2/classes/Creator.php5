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
 *	@version		$Id: Creator.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Creates Documentation Files from Parser Data.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1
 *	@uses			Folder_RecursiveIterator
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Clock
 *	@uses			Alg_Text_Trimmer
 *	@uses			DocCreator_Core_Environment
 *	@uses			Builder_HTML_CM2_Site_Control
 *	@uses			Builder_HTML_CM2_Site_Package
 *	@uses			Builder_HTML_CM2_Class_Builder
 *	@uses			Builder_HTML_CM2_Interface_Builder
 *	@uses			Builder_HTML_CM2_File_Builder
 *	@uses			Builder_HTML_CM2_Site_Builder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Creator.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
class Builder_HTML_CM2_Creator
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Environment	$env			Environment Object 
	 *	@param		XML_Element					$builder		XML Node from Config of Builder to apply
	 *	@param		bool						$options		Flag: be verbose
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Environment $env, XML_Element $builder, $verbose = NULL )
	{
		$options		= array( 'creator.verbose' => $verbose );
		$this->env		= $env;
		$this->config	= $this->env->config;
		$this->env->openBuilder( $builder );
		$this->env->load();

		$this->siteBuilder	= new Builder_HTML_CM2_Site_Builder( $this->env );

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

		Builder_HTML_CM2_Abstract::removeFiles( $pathTarget, '/^.+$/' );							// remove formerly copied resource files

		$index	= new Folder_RecursiveIterator( $pathSource );
		$length	= strlen( $pathSource );
		if( $this->env->verbose )
			$this->env->out->sameLine( "Copying ".$label );
		foreach( $index as $entry )
		{
			$name	= substr( $entry->getPathname(), $length );
			if( $entry->isFile() )
			{
				if( preg_match( "@\.skip@i", $entry->getPathname() ) )
					continue;
#				if( $this->env->verbose )
#					remark( "Copying: ".Alg_Text_Trimmer::trimCentric( $pathTarget.$name, 70 ) );
				if( 1 || !file_exists( $pathTarget.$name ) )
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
		Builder_HTML_CM2_Abstract::removeFiles( $pathTarget, '/^category\..+\.html$/' );			// remove formerly generated category files

		$builder	= new Builder_HTML_CM2_Site_Category( $this->env );
		foreach( $this->env->tree->getPackages() as $category )
		{
			$categoryId	= $category->getId();
			$fileName	= $prefix.$categoryId.".html";
			$view		= $builder->buildView( $category );
			if( $this->env->verbose )
				$this->env->out->sameLine( "Creating category: ".$categoryId );
			file_put_contents( $pathTarget.$fileName, $view );
		}
	}

	protected function createFiles()
	{
		$clock		= new Alg_Time_Clock;
		$pathTarget	= $this->pathTarget;
		Builder_HTML_CM2_Abstract::removeFiles( $pathTarget, '/^(class|interface)\..+\.html$/' );	// remove formerly generated class and interface files

		$fileBuilder		= new Builder_HTML_CM2_File_Builder( $this->env );
		$classBuilder		= new Builder_HTML_CM2_Class_Builder( $this->env, $fileBuilder );
		$interfaceBuilder	= new Builder_HTML_CM2_Interface_Builder( $this->env, $fileBuilder );

		if( $this->env->verbose ){
			$total	= 0;
			$count	= 0;
			$this->env->out->newLine();
			foreach( $this->env->data->getFiles() as $fileName => $file ){
				$total	+= count( $file->getClasses() );
				$total	+= count( $file->getInterfaces() );
			}
		}
		
		
		foreach( $this->env->data->getFiles() as $fileName => $file )
		{
			$clock2		= new Alg_Time_Clock;
			if( $file->hasClasses() )
			{
				foreach( $file->getClasses() as $class )
				{
					$count++;
					$classId	= $class->getId();
					$docFile	= $pathTarget.'class.'.$classId.".html";
					if( $this->env->verbose ){
						$percentage	= str_pad( round( $count / $total * 100 ), 2, " ", STR_PAD_LEFT );
						$this->env->out->sameLine( "Create (".$percentage."%) ".$classId );
					}
					$view		= $classBuilder->buildView( $file, $class );
					file_put_contents( $docFile, $view );
				}
			}
			if( $file->hasInterfaces() )
			{
				foreach( $file->getInterfaces() as $interface )
				{
					$count++;
					$interfaceId	= $interface->getId();
					$docFile		= $pathTarget.'interface.'.$interfaceId.".html";
					if( $this->env->verbose ){
						$percentage	= str_pad( round( $count / $total * 100 ), 2, " ", STR_PAD_LEFT );
						$this->env->out->sameLine( "Creating (".$percentage."%) ".$interfaceId );
					}
					$view		= $interfaceBuilder->buildView( $file, $interface );
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
		if( $this->env->verbose ){
			$this->env->out->sameLine( "Creating done." );
			$this->env->out->newLine();
		}
		$this->env->timeBuild	= $clock->stop( 6, 0 );
	}
	
	private function createPackageRecursive( ADT_PHP_Category $superPackage, $prefix = "package." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new Builder_HTML_CM2_Site_Package( $this->env );
		foreach( $superPackage->getPackages() as $package )
		{
			$packageId	= $package->getId();
			$fileName	= $prefix.$packageId.".html";
			$view		= $builder->buildView( $package );
			if( $this->env->verbose )
				$this->env->out->sameLine( "Creating package: ".$packageId );
			file_put_contents( $pathTarget.$fileName, $view );
			$this->createPackageRecursive( $package, $prefix );
		}
	}
	
	protected function createPackages( $prefix = "package." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		Builder_HTML_CM2_Abstract::removeFiles( $pathTarget, '/^package\..+\.html$/' );				// remove formerly generated package files

		$builder	= new Builder_HTML_CM2_Site_Category( $this->env );
		foreach( $this->env->tree->getPackages() as $category )
			$this->createPackageRecursive( $category, $prefix );
	}
	
	private function createSites()
	{
		$builder	= new Builder_HTML_CM2_Site_Builder( $this->env );
		$builder->createSites();
		if( $this->env->verbose ){
			$this->env->out->sameLine( "Sites created." );
			$this->env->out->newLine();
		}
	}
}
?>