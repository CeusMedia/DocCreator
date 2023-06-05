<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Creates Documentation Files from Parser Data.
 *
 *	Copyright (c) 2008-2023 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML;

use CeusMedia\Common\Alg\Time\Clock as Clock;
use CeusMedia\DocCreator\Builder\Abstraction as AbstractBuilder;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as AbstractHtmlBuilder;
use CeusMedia\PhpParser\Structure\Category_ as PhpCategory;
use ReflectionException;

/**
 *	Creates Documentation Files from Parser Data.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Creator extends AbstractBuilder
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onConstruct(): void
	{
		$this->createFiles();
		$this->createPackages();
		$this->createCategories();
		$this->createSites();
		$this->copyResourcesRecursive( $this->pathTheme.'js/', 'js/', "JavaScripts" );
		$this->copyResourcesRecursive( $this->pathTheme.'css/', 'css/', "Stylesheets" );
		$this->copyResourcesRecursive( $this->pathTheme.'images/', "images/", "Images" );
		$this->copyResourcesRecursive( $this->pathTheme.'fonts/', "fonts/", "Fonts" );
		$this->env->out->sameLine( "Copy done." );
	}

	protected function createCategories( string $prefix = "category." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		AbstractHtmlBuilder::removeFiles( $pathTarget, '/^category\..+\.html$/' );			// remove formerly generated category files

		$builder	= new Site\Category( $this->env );
		foreach( $this->env->tree->getPackages() as $category ){
			$categoryId	= $category->getId();
			$fileName	= $prefix.$categoryId.".html";
			$view		= $builder->buildView( $category );
			$this->env->out->sameLine( "Creating category: ".$categoryId );
			file_put_contents( $pathTarget.$fileName, $view );
		}
	}

	protected function createFiles()
	{
		$clock		= new Clock;
		$pathTarget	= $this->pathTarget;
		AbstractHtmlBuilder::removeFiles( $pathTarget, '/^(class|interface)\..+\.html$/' );	// remove formerly generated class and interface files

		$classBuilder		= new Classes\Builder( $this->env );
		$interfaceBuilder	= new Interfaces\Builder( $this->env );

		$total	= 0;
		$count	= 0;
		$this->env->out->newLine();
		foreach( $this->env->data->getFiles() as $file ){
			$total	+= count( $file->getClasses() );
			$total	+= count( $file->getInterfaces() );
		}

		foreach( $this->env->data->getFiles() as $file ){
			$clock2		= new Clock;
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

	protected function createPackages( string $prefix = "package." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		AbstractHtmlBuilder::removeFiles( $pathTarget, '/^package\..+\.html$/' );				// remove formerly generated package files
		foreach( $this->env->tree->getPackages() as $category )
			$this->createPackageRecursive( $category, $prefix );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	private function createSites()
	{
		$builder	= new Site\Builder( $this->env );
		$builder->createSites();
		$this->env->out->sameLine( "Sites created." );
		$this->env->out->newLine();
	}

	private function createPackageRecursive( PhpCategory $superPackage, string $prefix = "package." )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new Site\Package( $this->env );
		foreach( $superPackage->getPackages() as $package ){
			$packageId	= $package->getId();
			$fileName	= $prefix.$packageId.".html";
			$view		= $builder->buildView( $package );
			$this->env->out->sameLine( "Creating package: ".$packageId );
			file_put_contents( $pathTarget.$fileName, $view );
			$this->createPackageRecursive( $package, $prefix );
		}
	}
}
