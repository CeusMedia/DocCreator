<?php
/**
 *	Class holding environmental Resources for all DocCreater Components.
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
 *	@package		DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Environment.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Class holding environmental Resources for all DocCreater Components.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			File_INI_Reader
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Environment.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 *	@todo			fix case sensitive packages/categories
 */
class DocCreator_Core_Environment{

	public $config;
	public $words;
	public $data;
	public $packageList;
#	public $upperCasePackages	= array();
	public $extensions;
	public $verbose				= FALSE;

	/**	@var	ADT_PHP_Category	$tree		... */
	public $tree;
	public $phpClasses			= array();
	public $tool				= array();

	public $path;
	/**	@var	XML_Element			$builder		Builder section of config XML */
	public $builder;

	protected $hasGzipSupport	= FALSE;

	/**
	 *	Constructur, reads Resources and stores locally.
	 *	@access		public
	 *	@param		DocCreator_Core_Configuration	$config			Configuration Array Object
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Configuration $config, $configTool, $out ){
		$this->config	=& $config;
		$this->tool		= $configTool;
		$this->out		= $out;
		$this->verbose	= $config->getVerbose();
		$this->path		= dirname( dirname( __DIR__ ) ).'/';

		$uri	= $this->path."config/php.classes.list";
		$this->phpClasses	= File_Reader::loadArray( $uri );

		$this->hasGzipSupport	= function_exists( 'gzopen' );
	}

	/**
	 *	Returns capitalized Version of Package Name.
	 *	@access		public
	 *	@param		string			$label			Package Label
	 *	@return		string
	 */
	public function capitalizePackageLabel( $label ){
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
	public function capitalizePackageName( $packageName, $separator = "_" ){
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
	public function getBuilderClassPath(){
		$format		= $this->builder->getAttribute( 'format' );
		$converter	= $this->builder->getAttribute( 'converter' );
		return 'Builder/'.$format.'/'.$converter."/";
	}

	/**
	 *	Returns Path to read Info File from for currently selected Builder.
	 *	@access		public
	 *	@param		string
	 */
	public function getBuilderDocumentsPath(){
		return $this->config->getBuilderDocumentsPath( $this->builder );
	}

	public function getBuilderFormat(){
		return $this->builder->getAttribute( 'format' );
	}

	public function getBuilderOptions(){
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
	public function getBuilderPlugins(){
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
	public function getBuilderTargetPath(){
		return $this->config->getBuilderTargetPath( $this->builder );
	}
	
	public function getBuilderTheme(){
		return $this->builder->getAttribute( 'theme' );
	}

	/**
	 *	Returns full Path to Builder Theme.
	 *	@access		public
	 *	@return		string
	 */
	public function getBuilderThemePath(){
		$format		= $this->getBuilderFormat();
		$theme		= $this->getBuilderTheme();
		return $this->path.'themes/'.$format.'/'.$theme.'/';
	}

	/**
	 *	Returns Class Object from Class Name if registered.
	 *	@access		public
	 *	@param		string				$className			Name of Class to find Data Object for
	 *	@param		ADT_PHP_Interface	$relatedArtefact	A related Class or Interface (for Package and Category Information)
	 *	@return		ADT_PHP_Class
	 */
	public function getClassFromClassName( $className, ADT_PHP_Interface $relatedArtefact ){
		return $this->data->getClassFromClassName( $className, $relatedArtefact );
	}

	/**
	 *	Returns Class Object from Class ID if registered.
	 *	@access		public
	 *	@param		string				$id					ID of Class to find Data Object for
	 *	@return		ADT_PHP_Class
	 */
	public function getClassFromId( $id ){
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
	public function getId( $key, $delimiter = "-" ){
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
	 *	@return		ADT_PHP_Interface
	 */
	public function getInterfaceFromId( $id ){
		return $this->data->getInterfaceFromId( $id );
	}

	/**
	 *	Returns Interface Object from Interface Name if registered.
	 *	@access		public
	 *	@param		string				$interfaceName		Name of Interface to find Data Object for
	 *	@param		ADT_PHP_Interface	$relatedArtefact	A related Class or Interface (for Package and Category Information)
	 *	@return		ADT_PHP_Interface
	 */
	public function getInterfaceFromInterfaceName( $interfaceName, ADT_PHP_Interface $relatedArtefact ){
		return $this->data->getInterfaceFromInterfaceName( $interfaceName, $relatedArtefact );
	}

	/**
	 *	Loads Data Container and reads Stucture for Builders.
	 *	@access		public
	 *	@return		void
	 */
	public function load(){
		$this->data	= $this->loadContainer( $this->config );										//  load Data Container from Serial
		$this->readStructureTree();																	//  build Category/Package View from Data Container
		$this->readPackageStructure();																//  extract a List of Packages
		$this->readClassIndex();																	//  extract a List of Classes
	}

	/**
	 *	Loads Data Container from Serial File.
	 *	@access		public
	 *	@return		ADT_PHP_Container
	 *	@throws		RuntimeException if neither Archive File Name nor Serial Name is set
	 */
	public function loadContainer(){
		$archive	= $this->config->getArchiveFileName();
		$serial		= $this->config->getSerialFileName();
		if( !empty( $archive ) && $this->hasGzipSupport ){
			$uri	= $this->path.$archive;
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
			$uri	= $this->path.$serial;
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
	 *	@param		XML_Element		$builder		Builder section of config XML
	 *	@return		void
	 */
	public function openBuilder( XML_Element $builder ){
		$this->builder	= $builder;
		$format			= $builder->getAttribute( 'format' );
		$theme			= $builder->getAttribute( 'theme' );
		$pathTheme		= $this->path.'themes/'.$format.'/'.$theme.'/';
		$fileLocales	= $pathTheme.'locales/'.$builder->language->getValue().".ini";
		$reader			= new File_INI_Reader( $fileLocales, TRUE );
		$this->words	= $reader->toArray();
	}

	/**
	 *	@todo		same algo is in Container, check which is deprecated
	 */
	private function readClassIndex(){
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

	private function readPackageStructure(){
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
		return;
	}

	/**
	 *	Build Category/Package View from Data Container, assigning found Classes and Interfaces in Tree Nodes.
	 *	@access		private
	 *	@return		void
	 */
	private function readStructureTree(){
		$this->tree	= new ADT_PHP_Category( "root" );
//		$this->tree->setPackage( 'default', new ADT_PHP_Category( 'default' ) );
		foreach( $this->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
#				remark( $class->getName().": ".$class->getCategory());
				if( !$class->getCategory() )
					continue;
				if( !$this->tree->hasPackage( $class->getCategory() ) )
					$this->tree->setPackage( $class->getCategory(), new ADT_PHP_Category( $class->getCategory() ) );
				if( !$class->getPackage() )
					continue;
				$category	= $this->tree->getPackage( $class->getCategory() );
				$name		= str_replace( ".", "_", $class->getPackage() );
				$parts		= explode( "_", $name );
				$name		= array_pop( $parts );
				$package	= new ADT_PHP_Package( $this->capitalizePackageLabel( $name ) );
				$package->addClass( $class );
				$category->setPackage( $class->getPackage(), $package );
			}
			foreach( $file->getInterfaces() as $interface ){
				if( !$interface->getCategory() )
					continue;
				if( !$this->tree->hasPackage( $interface->getCategory() ) )
					$this->tree->setPackage( $interface->getCategory(), new ADT_PHP_Category( $interface->getCategory() ) );
				if( !$interface->getPackage() )
					continue;
				$category	= $this->tree->getPackage( $interface->getCategory() );
				$name		= str_replace( ".", "_", $interface->getPackage() );
				$parts		= explode( "_", $name );
				$name		= array_pop( $parts );
				$package	= new ADT_PHP_Package( $this->capitalizePackageLabel( $name ) );
				$package->addInterface( $interface);
				$category->setPackage( $interface->getPackage(), $package );
			}
		}
#		$this->printTree( $this->tree );
	}

	/**
	 *	@deprecated		removed instantly because of (meanwhile removed) call to die-function at the end
	 *	@todo			check for method calls and remove
	 *	@todo			this method could be migrated to a DocCreator Browser User Interface
	 */
	public function printTree( $category, $level = 0 ){
		if( $category instanceof ADT_PHP_Category ){
			foreach( $category->getCategories() as $cat ){
				remark( str_repeat( "  ", $level * 4).$cat->getLabel() );
				$this->printTree( $cat, $level + 1 );
			}
			foreach( $category->getPackages() as $pack ){
				remark( str_repeat( "  ", $level * 4).$pack->getLabel() );
			}
		}
		else if( $category instanceof ADT_PHP_Package ){
			foreach( $category->getPackages() as $pack ){
				remark( str_repeat( "  ", $level * 4).$pack->getLabel() );
				$this->printTree( $pack, $level + 1 );
			}
		}
	}


	/**
	 *	Stores collected File/Class Data as Serial File or Archive File.
	 *	@access		protected
	 *	@param		ADT_PHP_Container	$data		Collected File / Class Data
	 *	@return		void
	 */
	public function saveContainer( ADT_PHP_Container $data ){
		$serial	= serialize( $data );
		if( !file_exists( $this->path ) )
			mkDir( $this->path, 0775, TRUE );

		$fileArchive	= $this->config->getArchiveFileName();
		$fileSerial		= $this->config->getSerialFileName();
		if( !empty( $fileArchive ) && $this->hasGzipSupport ){
			$uri	= $this->path.$fileArchive;
			$gz		= gzopen( $uri, 'w9' );
			gzwrite( $gz, $serial );
			gzclose( $gz );
		}
		else if( !empty( $fileSerial ) ){
			$uri	= $this->path.$fileSerial;
			file_put_contents( $uri, $serial );
		}
	}
}
?>
