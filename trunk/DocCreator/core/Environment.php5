<?php
/**
 *	Class holding environmental Resources for all DocCreater Components.
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
 *	@package		DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Environment.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
import( 'de.ceus-media.file.ini.Reader' );
/**
 *	Class holding environmental Resources for all DocCreater Components.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			File_INI_Reader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Environment.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
class DocCreator_Core_Environment
{
	public $config;
	public $words;
	public $data;
	public $classList;
	public $fileList;
	public $packageList;
	public $packageTree;
#	public $upperCasePackages	= array();
	public $extensions;
	public $verbose				= FALSE; 
	
	public $tree;
	public $phpClasses			= array();

	/**
	 *	Constructur, reads Resources and stores locally.
	 *	@access		public
	 *	@param		DocCreator_Core_Configuration	$config			Configuration Array Object 
	 *	@param		ArrayObject						$options		Options Array Object to overwrite Configuration
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Configuration $config, ArrayObject $options )
	{
		$this->config	=& $config;
		$this->verbose	= $config->getVerbose();

		$this->path		= $_ENV['TMP']."/";

		$uri	= dirname( dirname( __FILE__ ) )."/config/php.classes.list";
		$this->phpClasses	= File_Reader::loadArray( $uri );

#		foreach( $options as $key => $value )
#			if( array_key_exists( $key, $config ) && $value !== NULL )
#				$config[$key]	= $value;
		
#		$packageNames	= $config['project.package.upperCase'];
#		$parts			= explode( ",", $packageNames );
#		foreach( $parts as $part )
#			if( trim( $part ) )
#				$this->upperCasePackages[]	= $part;

	}

	public function openBuilder( XML_Element $builder )
	{
		$this->builder	= $builder;
		$fileLocales	= $this->getBuilderClassPath().'locales/'.$builder->language->getValue().".ini";
		$reader			= new File_INI_Reader( $fileLocales, TRUE );
		$this->words	= $reader->toArray();
	}

	public function getBuilderTargetPath()
	{
		return $this->config->getBuilderTargetPath( $this->builder );
	}

	public function getBuilderDocumentsPath()
	{
		return $this->config->getBuilderDocumentsPath( $this->builder );
	}
	
	public function load()
	{
		$data	= new ADT_PHP_Container();
		$this->data	= $this->loadContainer( $this->config );
		$this->readStructureTree();
		$this->readPackageStructure();
		$this->readClassIndex();

#		$this->extensions		= explode( ",", $config['project.extensions'] );
#		$this->extensions		= implode( "|", $this->extensions );
		$this->fileList			= array();
		$this->classList		= array();
/*		foreach( $this->data->getFiles() as $fileName => $file)
		{
			$className	= $this->getClassNameFromFileName( $fileName );
			$this->classList[$className] = $fileName;
			$this->fileList[$fileName] = $className;
		}
*/	}

	/**
	 *	Returns capitalized Version of Package Name.
	 *	@access		public
	 *	@param		string			$label			Package Label
	 *	@return		string
	 */
	public function capitalizePackageLabel( $label )
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
	public function capitalizePackageName( $packageName, $separator = "_" )
	{
#		$packageParts	= explode( $separator, $packageName );
#		foreach( $packageParts as $nr => $part )
#		{
#			$part	= ucFirst( $part );
#			if( in_array( $part, $this->upperCasePackages ) )
#				$part	= strtoupper( $part );
#			$packageParts[$nr]	= $part;
#		}
#		$packageName	= implode( "_", $packageParts );
		return $packageName;
	}

	public function getClassFromClassName( $className, ADT_PHP_Class $relatedClass )
	{
		return $this->data->getClassFromClassName( $className, $relatedClass );
	}

	public function getClassFromId( $id )
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
	public function getId( $key, $delimiter = "-" )
	{
		$key	= preg_replace( '@\.('.$this->extensions.')$@i', "", $key );
		$key	= str_replace( ".", $delimiter, $key );
		$key	= str_replace( "/", $delimiter, $key );
		$key	= str_replace( "_", $delimiter, $key );
		$key	= strtolower( $key );
		return $key;
	}
	
	/**
	 *	Returns Path to Builder Classes.
	 *	@access		public
	 *	@return		string			Path to Builder Classes
	 */
	public function getBuilderClassPath()
	{
		$format		= $this->builder->getAttribute( 'format' );
		$converter	= $this->builder->getAttribute( 'converter' );
		return 'builder/'.$format.'/'.$converter."/";
	}
	
	public function getBuilderTheme()
	{
		return $this->builder->getAttribute( 'theme' );
	}

	public function loadContainer()
	{
		$archive	= $this->config->getArchiveFileName();
		$serial		= $this->config->getSerialFileName();
		if( !empty( $archive ) )
		{
			$uri	= $this->path.$archive;
			if( file_exists( $uri ) )
			{
				$serial	= "";
				if( $fp = gzopen( $uri, "r" ) )
				{
					while( !gzeof( $fp ) )
						$serial	.= gzgets( $fp, 4096 );
					$data	= unserialize( $serial );
					gzclose( $fp );
				}				
				return $data;
			}
		}
		if( !empty( $serial ) )
		{
			$uri	= $this->path.$serial;
			if( file_exists( $uri ) )
			{
				$serial	= file_get_contents( $uri );
				$data	= unserialize( $serial );
				return $data;
			}
		}
		throw new RuntimeException( 'No data file existing' );
	}

	private function readClassIndex()
	{
		foreach( $this->data->getFiles() as $fileName => $file )
		{
			foreach( $file->getClasses() as $className => $class )
			{
#				$category	= $this->config->data->['project.category.default'];
#				$package	= $this->config['project.package.default'];

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
		foreach( $this->data->getFiles() as $fileName => $file )
		{
			foreach( $file->getClasses() as $className => $class )
			{
				$category	= $class->getCategory() ? $class->getCategory() : 'default';
				$package	= $class->getPackage() ? $class->getPackage() : 'default';
				$packageId	= $category."-".$package;
				$list[$packageId]	= $this->tree->getPackage( $category."_".$package );
			}
		}
		$this->packageList	= $list;
		return;
	}

	private function readStructureTree()
	{
		$this->tree	= new ADT_PHP_Category( "root" );
		foreach( $this->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				if( !$class->getCategory() )
					continue;
				if( !$this->tree->hasPackage( $class->getCategory() ) )
					$this->tree->setPackage( $class->getCategory(), new ADT_PHP_Category( $class->getCategory() ) );
				if( !$class->getPackage() )
					continue;
				$category	= $this->tree->getPackage( $class->getCategory() );
				$name		= str_replace( ".", "_", $class->getPackage() );
				$name		= array_pop( explode( "_", $name ) );
				$package	= new ADT_PHP_Package( $this->capitalizePackageLabel( $name ) );
				$package->setClass( $class->getName(), $class );
				$category->setPackage( $class->getPackage(), $package );
			}
		}
	}

	/**
	 *	Stores collected File/Class Data as Serial File or Archive File.
	 *	@access		protected
	 *	@param		ADT_PHP_Container	$data		Collected File / Class Data
	 *	@return		void
	 */
	public function saveContainer( ADT_PHP_Container $data )
	{
		$serial	= serialize( $data );
#		if( !file_exists( $this->path ) )
#			mkDir( $this->path, 0775, TRUE );
		
		$fileArchive	= $this->config->getArchiveFileName();
		$fileSerial		= $this->config->getSerialFileName();
		if( !empty( $fileArchive ) )
		{
			$uri	= $this->path.$fileArchive;
			$gz		= gzopen( $uri, 'w9' );
			gzwrite( $gz, $serial );
			gzclose( $gz );
		}
		else if( !empty( $fileSerial ) )
		{
			$uri	= $this->path.$fileSerial;
			file_put_contents( $uri, $serial );
		}
	}
}
?>