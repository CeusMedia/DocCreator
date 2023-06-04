<?php
/**
 *	Class holding environmental Resources for all DocCreater Components.
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
 *	@package		CeusMedia_DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Core;

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\INI\Reader as IniFileReader;
use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\PhpParser\Structure\Category_ as PhpCategory;
use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\Container_ as PhpContainer;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;
use CeusMedia\PhpParser\Structure\Package_ as PhpPackage;
use RuntimeException;

/**
 *	Class holding environmental Resources for all DocCreater Components.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Core
 *	@uses			IniFileReader
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@todo			fix case sensitive packages/categories
 */
class Environment
{
	public $config;
	public $words;
	public $data;
	public $packageList;
#	public $upperCasePackages	= array();
	public $extensions;
	public $verbose				= FALSE;

	/**	@var	PhpCategory	$tree		... */
	public $tree;
	public $phpClasses			= array();
	public $tool				= array();

	public $path;
	/**	@var	XmlElement			$builder		Builder section of config XML */
	public $builder;

	protected $hasGzipSupport	= FALSE;

	/**
	 *	Constructur, reads Resources and stores locally.
	 *	@access		public
	 *	@param		Configuration	$config			Configuration Array Object
	 *	@return		void
	 */
	public function __construct( Configuration $config, $configTool, $out )
	{
		$this->config	= $config;
		$this->tool		= $configTool;
		$this->out		= $out;
		$this->verbose	= $config->getVerbose();
		$this->path		= dirname( dirname( __DIR__ ) ).'/';

		$uri	= $this->path."config/php.classes.list";
		$this->phpClasses	= FileReader::loadArray( $uri );

		$this->hasGzipSupport	= function_exists( 'gzopen' );

		if( !file_exists( $pathTmp = $this->config->getTempPath() ) )
			throw new RuntimeException( "Configured path for temporary files (".$pathTmp.") is not existing" );
		if( !file_exists( $pathLog = $this->config->getLogPath() ) )
			throw new RuntimeException( "Configured path for log files (".$pathLog.") is not existing" );
	}

	/**
	 *	Returns capitalized Version of Package Name.
	 *	@access		public
	 *	@param		string			$label			Package Label
	 *	@return		string
	 */
	public function capitalizePackageLabel( string $label ): string
	{
		return $label;
#		if( in_array( $label, $this->upperCasePackages ) )
#			return strtoupper( $label );
#		return $label;
	}

	/**
	 *	Returns capitalized Version of Package Name.
	 *	@access		public
	 *	@param		string			$packageName
	 *	@return		string
	 */
	public function capitalizePackageName( string $packageName, string $separator = "_" ): string
	{
#		$packageParts	= explode( $separator, $packageName );
#		foreach( $packageParts as $nr => $part )
#		{
#			$part	= ucFirst( $part );
#			if( in_array( $part, $this->upperCasePackages ) )
#				$part	= strtoupper( $part );
#			$packageParts[$nr]	= $part;
#		}
#		$packageName	= implode( $separator, $packageParts );
		return $packageName;
	}

	/**
	 *	Returns Path to Builder Classes.
	 *	@access		public
	 *	@return		string			Path to Builder Classes
	 */
	public function getBuilderClassPath(): string
	{
		$format		= $this->builder->getAttribute( 'format' );
		$converter	= $this->builder->getAttribute( 'converter' );
		return 'Builder/'.$format.'/'.$converter."/";
	}

	/**
	 *	Returns Path to read Info File from for currently selected Builder.
	 *	@access		public
	 *	@param		string
	 */
	public function getBuilderDocumentsPath(): string
	{
		return $this->config->getBuilderDocumentsPath( $this->builder );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string
	 */
	public function getBuilderFormat(): string
	{
		return $this->builder->getAttribute( 'format' );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		array
	 */
	public function getBuilderOptions(): array
	{
		$list	= array();
		foreach( $this->config->getBuilderOptions( $this->builder ) as $option )
			$list[$option->getAttribute( 'name' )]	= trim( $option );
		return $list;
	}

	/**
	 *	Returns map of plugins and their attributes (used as options later).
	 *	@access		public
	 *	@return		array
	 */
	public function getBuilderPlugins(): array
	{
		$list	= array();
		foreach( $this->config->getBuilderPlugins( $this->builder ) as $plugin )
			if( trim( $plugin ) )
				$list[trim( $plugin )]	= $plugin->getAttributes();
		return $list;
	}

	/**
	 *	Returns Path to save created Files in for currently selected Builder.
	 *	@access		public
	 *	@param		string
	 */
	public function getBuilderTargetPath(): string
	{
		return $this->config->getBuilderTargetPath( $this->builder );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string
	 */
	public function getBuilderTheme(): string
	{
		return $this->builder->getAttribute( 'theme' );
	}

	/**
	 *	Returns full Path to Builder Theme.
	 *	@access		public
	 *	@return		string
	 */
	public function getBuilderThemePath(): string
	{
		$format		= $this->getBuilderFormat();
		$theme		= $this->getBuilderTheme();
		return $this->path.'themes/'.$format.'/'.$theme.'/';
	}

	/**
	 *	Returns Class Object from Class Name if registered.
	 *	@access		public
	 *	@param		string				$className			Name of Class to find Data Object for
	 *	@param		PhpInterface	$relatedArtefact	A related Class or Interface (for Package and Category Information)
	 *	@return		PhpClass
	 */
	public function getClassFromClassName( string $className, PhpInterface $relatedArtefact ): PhpClass
	{
		return $this->data->getClassFromClassName( $className, $relatedArtefact );
	}

	/**
	 *	Returns Class Object from Class ID if registered.
	 *	@access		public
	 *	@param		string				$id					ID of Class to find Data Object for
	 *	@return		PhpClass
	 */
	public function getClassFromId( string $id ): PhpClass
	{
		return $this->data->getClassFromId( $id );
	}

	/**
	 *	Builds ID from Class or File Key for Links etc.
	 *	@access		public
	 *	@param		string			$key			Key of Class or File
	 *	@param		string			$delimiter		Delimiter Sign to be set in instead of . and /
	 *	@return		string
	 *	@deprecated	use [DataObject]->getId() instead
	 */
	public function getId( string $key, string $delimiter = "-" ): string
	{
		$key	= preg_replace( '@\.('.$this->extensions.')$@i', "", $key );
		$key	= str_replace( ".", $delimiter, $key );
		$key	= str_replace( "/", $delimiter, $key );
		$key	= str_replace( "_", $delimiter, $key );
		$key	= strtolower( $key );
		return $key;
	}

	/**
	 *	Returns Interface Object from Interface ID if registered.
	 *	@access		public
	 *	@param		string				$id					ID of Interface to find Data Object for
	 *	@return		PhpInterface
	 */
	public function getInterfaceFromId( string $id ): PhpInterface
	{
		return $this->data->getInterfaceFromId( $id );
	}

	/**
	 *	Returns Interface Object from Interface Name if registered.
	 *	@access		public
	 *	@param		string			$interfaceName		Name of Interface to find Data Object for
	 *	@param		PhpInterface	$relatedArtefact	A related Class or Interface (for Package and Category Information)
	 *	@return		PhpInterface
	 */
	public function getInterfaceFromInterfaceName( string $interfaceName, PhpInterface $relatedArtefact ): PhpInterface
	{
		return $this->data->getInterfaceFromInterfaceName( $interfaceName, $relatedArtefact );
	}

	/**
	 *	Loads Data Container and reads Stucture for Builders.
	 *	@access		public
	 *	@return		void
	 */
	public function load()
	{
		$this->data	= $this->loadContainer( $this->config );										//  load Data Container from Serial
		$this->readStructureTree();																	//  build Category/Package View from Data Container
		$this->readPackageStructure();																//  extract a List of Packages
		$this->readClassIndex();																	//  extract a List of Classes
	}

	/**
	 *	Loads Data Container from Serial File.
	 *	@access		public
	 *	@return		PhpContainer
	 *	@throws		RuntimeException if neither Archive File Name nor Serial Name is set
	 */
	public function loadContainer(): PhpContainer
	{
//		throw new RuntimeException( "Continue Mode (=loading container from temp archive) is disabled for now" );
		$archive	= $this->config->getArchiveFileName();
		$serial		= $this->config->getSerialFileName();
		if( !empty( $archive ) && $this->hasGzipSupport ){
			$uri	= $archive;
			if( file_exists( $uri ) ){
				$serial	= "";
				if( $fp = gzopen( $uri, "r" ) ){
					while( !gzeof( $fp ) )
						$serial	.= gzgets( $fp, 4096 );
					$data	= unserialize( $serial );
					gzclose( $fp );
				}
			}
			return $data;
		}
		if( !empty( $serial ) ){
			$uri	= $serial;
			if( file_exists( $uri ) ){
				$serial	= file_get_contents( $uri );
				$data	= unserialize( $serial );
				return $data;
			}
		}
		throw new RuntimeException( 'No data file existing - you need to parse' );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		XmlElement		$builder		Builder section of config XML
	 *	@return		void
	 */
	public function openBuilder( XmlElement $builder )
	{
		$this->builder	= $builder;
		$format			= $builder->getAttribute( 'format' );
		$theme			= $builder->getAttribute( 'theme' );
		$pathTheme		= $this->path.'themes/'.$format.'/'.$theme.'/';
		$fileLocales	= $pathTheme.'locales/'.$builder->language->getValue().".ini";
		$reader			= new IniFileReader( $fileLocales, TRUE );
		$this->words	= $reader->toArray();
	}


	/**
	 *	@deprecated		removed instantly because of (meanwhile removed) call to die-function at the end
	 *	@todo			check for method calls and remove
	 *	@todo			this method could be migrated to a DocCreator Browser User Interface
	 */
	public function printTree( $category, int $level = 0 )
	{
		if( $category instanceof PhpCategory ){
			foreach( $category->getCategories() as $cat ){
				remark( str_repeat( "  ", $level * 4).$cat->getLabel() );
				$this->printTree( $cat, $level + 1 );
			}
			foreach( $category->getPackages() as $pack ){
				remark( str_repeat( "  ", $level * 4).$pack->getLabel() );
			}
		}
		else if( $category instanceof PhpPackage ){
			foreach( $category->getPackages() as $pack ){
				remark( str_repeat( "  ", $level * 4).$pack->getLabel() );
				$this->printTree( $pack, $level + 1 );
			}
		}
	}

	/**
	 *	Stores collected File/Class Data as Serial File or Archive File.
	 *	@access		protected
	 *	@param		PhpContainer	$data		Collected File / Class Data
	 *	@return		void
	 */
	public function saveContainer( PhpContainer $data )
	{
//		return TRUE;
		$serial	= serialize( $data );
		if( !file_exists( $this->path ) )
			mkDir( $this->path, 0775, TRUE );

		$fileArchive	= $this->config->getArchiveFileName();
		$fileSerial		= $this->config->getSerialFileName();
		if( !empty( $fileArchive ) && $this->hasGzipSupport ){
			$uri	= $fileArchive;
			$gz		= gzopen( $uri, 'w9' );
			gzwrite( $gz, $serial );
			gzclose( $gz );
		}
		else if( !empty( $fileSerial ) ){
			$uri	= $fileSerial;
			file_put_contents( $uri, $serial );
		}
	}

	/**
	 *	@todo		same algo is in Container, check which is deprecated
	 */
	private function readClassIndex()
	{
		foreach( $this->data->getFiles() as $fileName => $file ){
			foreach( $file->getClasses() as $className => $class ){
				$category	= 'default';
				$package	= 'default';

				$category	= $class->getCategory() ? $class->getCategory() : $category;
				$package	= $class->getPackage() ? $class->getPackage() : $package;
				$this->classNameList[$class->getName()][$category][$package]	= $class;
				$this->classIdList[$class->getId()]	= $class;
			}
		}
	}

	private function readPackageStructure()
	{
		$list	= array();
		foreach( $this->data->getFiles() as $fileName => $file ){
			foreach( $file->getClasses() as $className => $class ){
				$category	= trim( $class->getCategory() ) ? trim( $class->getCategory() ) : 'default';
				$package	= trim( $class->getPackage() ) ? trim( $class->getPackage() ) : 'default';
				$packageId	= $category."-".$package;
				$list[$packageId]	= $this->tree->getPackage( $category."_".$package );
			}
		}
		ksort( $list );
		$this->packageList	= $list;
	}

	/**
	 *	Build Category/Package View from Data Container, assigning found Classes and Interfaces in Tree Nodes.
	 *	@access		private
	 *	@return		void
	 */
	private function readStructureTree()
	{
		$this->tree	= new PhpCategory( "root" );
//		$this->tree->setPackage( 'default', new PhpCategory( 'default' ) );
		foreach( $this->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
#				remark( $class->getName().": ".$class->getCategory());
				if( !$class->getCategory() )
					continue;
				if( !$this->tree->hasPackage( $class->getCategory() ) )
					$this->tree->setPackage( $class->getCategory(), new PhpPackage( $class->getCategory() ) );
				if( !$class->getPackage() )
					continue;
				$category	= $this->tree->getPackage( $class->getCategory() );
				$name		= str_replace( ".", "_", $class->getPackage() );
				$parts		= explode( "_", $name );
				$name		= array_pop( $parts );
				$package	= new PhpPackage( $this->capitalizePackageLabel( $name ) );
				$package->addClass( $class );
				$category->setPackage( $class->getPackage(), $package );
			}
			foreach( $file->getInterfaces() as $interface ){
				if( !$interface->getCategory() )
					continue;
				if( !$this->tree->hasPackage( $interface->getCategory() ) )
					$this->tree->setPackage( $interface->getCategory(), new PhpCategory( $interface->getCategory() ) );
				if( !$interface->getPackage() )
					continue;
				$category	= $this->tree->getPackage( $interface->getCategory() );
				$name		= str_replace( ".", "_", $interface->getPackage() );
				$parts		= explode( "_", $name );
				$name		= array_pop( $parts );
				$package	= new PhpPackage( $this->capitalizePackageLabel( $name ) );
				$package->addInterface( $interface);
				$category->setPackage( $interface->getPackage(), $package );
			}
		}
#		$this->printTree( $this->tree );
	}
}
