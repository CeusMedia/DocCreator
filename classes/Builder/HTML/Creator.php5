<?php
/**
 *	Creates Documentation Files from Parser Data.
 *
 *	Copyright (c) 2008-2013 Christian Würker (ceusmedia.de)
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
 *	@package		DocCreator_Builder_HTML
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Creator.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Creates Documentation Files from Parser Data.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML
 *	@uses			Folder_RecursiveIterator
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Clock
 *	@uses			Alg_Text_Trimmer
 *	@uses			DocCreator_Core_Environment
 *	@uses			DocCreator_Builder_HTML_Site_Control
 *	@uses			DocCreator_Builder_HTML_Site_Package
 *	@uses			DocCreator_Builder_HTML_Class_Builder
 *	@uses			DocCreator_Builder_HTML_Interface_Builder
 *	@uses			DocCreator_Builder_HTML_File_Builder
 *	@uses			DocCreator_Builder_HTML_Site_Builder
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Creator.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Creator{

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Environment	$env			Environment Object 
	 *	@param		XML_Element					$builder		XML Node from Config of Builder to apply
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Environment $env, XML_Element $builder ){
		$this->env		= $env;
		$this->config	= $this->env->config;
		$this->env->openBuilder( $builder );
		$this->env->load();
		$this->pathTarget	= $this->env->getBuilderTargetPath();
		if( !file_exists( $this->pathTarget ) )
			mkDir( $this->pathTarget, 0775, TRUE );
		$this->createFiles();
		$this->createPackages();
		$this->createCategories();
		$this->createSites();
		$pathTheme	= 'themes/'.$this->env->getBuilderTheme().'/';
		$this->copyResourcesRecursive( $pathTheme.'js/', 'js/', "JavaScripts" );
		$this->copyResourcesRecursive( $pathTheme.'css/', 'css/', "Stylesheets" );
		$this->copyResourcesRecursive( $pathTheme.'images/', "images/", "Images" );
		$this->env->out->sameLine( "Copy done." );
	}

	protected function copyResourcesRecursive( $pathSource, $pathTarget, $label ){
		$pathSource	= $this->env->path.$pathSource;
		$pathTarget	= $this->pathTarget.$pathTarget;
		if( !file_exists( $pathTarget ) )
			mkDir( $pathTarget, 0775, TRUE );

		DocCreator_Builder_HTML_Abstract::removeFiles( $pathTarget, '/^.+$/' );							// remove formerly copied resource files

		$index	= new Folder_RecursiveIterator( $pathSource );
		$length	= strlen( $pathSource );
		if( $this->env->verbose )
			$this->env->out->sameLine( "Copying ".$label );
		foreach( $index as $entry ){
			$name	= substr( $entry->getPathname(), $length );
			if( $entry->isFile() ){
				if( preg_match( "@\.skip@i", $entry->getPathname() ) )
					continue;
				if( !@copy( $entry->getPathname(), $pathTarget.$name ) )
					throw new RuntimeException( 'File "'.$entry->getPathname().'" could not be copied to "'.$pathTarget.$name.'"' ); 
			}
			else if( $entry->isDir() && !file_exists( $pathTarget.$name ) )
				mkDir( $pathTarget.$name );
		}	
	}
	
	protected function createCategories( $prefix = "category." ){
		$pathTarget	= $this->env->getBuilderTargetPath();
		DocCreator_Builder_HTML_Abstract::removeFiles( $pathTarget, '/^category\..+\.html$/' );			// remove formerly generated category files

		$builder	= new DocCreator_Builder_HTML_Site_Category( $this->env );
		foreach( $this->env->tree->getPackages() as $category ){
			$categoryId	= $category->getId();
			$fileName	= $prefix.$categoryId.".html";
			$view		= $builder->buildView( $category );
			$this->env->out->sameLine( "Creating category: ".$categoryId );
			file_put_contents( $pathTarget.$fileName, $view );
		}
	}

	protected function createFiles(){
		$clock		= new Alg_Time_Clock;
		$pathTarget	= $this->pathTarget;
		DocCreator_Builder_HTML_Abstract::removeFiles( $pathTarget, '/^(class|interface)\..+\.html$/' );	// remove formerly generated class and interface files

		$fileBuilder		= new DocCreator_Builder_HTML_File_Builder( $this->env );
		$classBuilder		= new DocCreator_Builder_HTML_Class_Builder( $this->env, $fileBuilder );
		$interfaceBuilder	= new DocCreator_Builder_HTML_Interface_Builder( $this->env, $fileBuilder );

		$total	= 0;
		$count	= 0;
		$this->env->out->newLine();
		foreach( $this->env->data->getFiles() as $fileName => $file ){
			$total	+= count( $file->getClasses() );
			$total	+= count( $file->getInterfaces() );
		}

		foreach( $this->env->data->getFiles() as $fileName => $file ){
			$clock2		= new Alg_Time_Clock;
			if( $file->hasClasses() ){
				foreach( $file->getClasses() as $class ){
					$count++;
					$classId	= $class->getId();
					$docFile	= $pathTarget.'class.'.$classId.".html";
					$percentage	= str_pad( round( $count / $total * 100 ), 2, " ", STR_PAD_LEFT );
					$this->env->out->sameLine( "Create (".$percentage."%) ".$classId );
					$view		= $classBuilder->buildView( $file, $class );
					file_put_contents( $docFile, $view );
				}
			}
			if( $file->hasInterfaces() ){
				foreach( $file->getInterfaces() as $interface ){
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
/*			else{
				$view		= $fileBuilder->buildView( $file );
				file_put_contents( $docFile, $view );
			}*/
			$file->timeBuild	= $clock2->stop( 6, 0 );
		}
		$this->env->out->sameLine( "Files created." );
		$this->env->out->newLine();
		$this->env->timeBuild	= $clock->stop( 6, 0 );
	}
	
	private function createPackageRecursive( ADT_PHP_Category $superPackage, $prefix = "package." ){
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new DocCreator_Builder_HTML_Site_Package( $this->env );
		foreach( $superPackage->getPackages() as $package ){
			$packageId	= $package->getId();
			$fileName	= $prefix.$packageId.".html";
			$view		= $builder->buildView( $package );
			$this->env->out->sameLine( "Creating package: ".$packageId );
			file_put_contents( $pathTarget.$fileName, $view );
			$this->createPackageRecursive( $package, $prefix );
		}
	}
	
	protected function createPackages( $prefix = "package." ){
		$pathTarget	= $this->env->getBuilderTargetPath();
		DocCreator_Builder_HTML_Abstract::removeFiles( $pathTarget, '/^package\..+\.html$/' );				// remove formerly generated package files
		foreach( $this->env->tree->getPackages() as $category )
			$this->createPackageRecursive( $category, $prefix );
	}
	
	private function createSites(){
		$builder	= new DocCreator_Builder_HTML_Site_Builder( $this->env );
		$builder->createSites();
		$this->env->out->sameLine( "Sites created." );
		$this->env->out->newLine();
	}
}
?>