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
 *	@package		DocCreator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Environment.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.file.ini.Reader' );
import( 'de.ceus-media.ui.Template' );
/**
 *	Class holding environmental Resources for all DocCreater Components.
 *	@category		cmTools
 *	@package		DocCreator
 *	@uses			File_INI_Reader
 *	@uses			UI_Template
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Environment.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Environment
{
	public $config;
	public $words;
	public $data;
	public $classList;
	public $fileList;
	public $packageList;
	public $packageTree;
	public $upperCasePackages	= array();
	public $extensions;
	public $verbose				= FALSE; 
	
	public $tree;
	public $phpClasses			= array();

	/**
	 *	Constructur, reads Resources and stores locally.
	 *	@access		public
	 *	@param		ArrayObject		$config			Configuration Array Object 
	 *	@param		ArrayObject		$options		Options Array Object to overwrite Configuration
	 *	@return		void
	 */
	public function __construct( ArrayObject $config, ArrayObject $options )
	{
		$this->config	=& $config;
		$this->verbose	= $config['creator.verbose'];
		foreach( $options as $key => $value )
			if( array_key_exists( $key, $config ) && $value !== NULL )
				$config[$key]	= $value;
		
		$pathLocales	= $this->getBuilderPath().'locales/';
		$reader			= new File_INI_Reader( $pathLocales.$config['doc.language'].".ini", TRUE );
		$this->words	= $reader->toArray();

		$packageNames	= $config['doc.upperCasePackages'];
		$parts			= explode( ",", $packageNames );
		foreach( $parts as $part )
			if( trim( $part ) )
				$this->upperCasePackages[]	= $part;

		$uri	= dirname( dirname( __FILE__ ) )."/config/php.classes.list";
		$this->phpClasses	= File_Reader::loadArray( $uri );

		$data	= new Model_Container();
		$this->data	= $data->load( $this->config );
		$this->readStructureTree();
		$this->readPackageStructure();
		$this->readClassIndex();

		$this->extensions		= explode( ",", $config['project.extensions'] );
		$this->extensions		= implode( "|", $this->extensions );
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
		if( in_array( $label, $this->upperCasePackages ) )
			return strtoupper( $label );
		return $label;
	}

	/**
	 *	Returns capitalized Version of Package Name.
	 *	@access		public
	 *	@param		string			$packageName
	 *	@return		string
	 */
	public function capitalizePackageName( $packageName, $separator = "_" )
	{
		$packageParts	= explode( $separator, $packageName );
		foreach( $packageParts as $nr => $part )
		{
			$part	= ucFirst( $part );
			if( in_array( $part, $this->upperCasePackages ) )
				$part	= strtoupper( $part );
			$packageParts[$nr]	= $part;
		}
		$packageName	= implode( "_", $packageParts );
		return $packageName;
	}

	public function getClassFromClassName( $className, Model_Class $relatedClass )
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
	 *	Returns URI of Template Name for a given Template Key and optional Template Folder.
	 *	@access		public
	 *	@param		string			$fileName		Template Key (without Extension)
	 *	@param		string			$packageName	Template Folder (without trailing Slash)
	 *	@return		string			File Name of Template File
	 */
	public function getTemplateFile( $fileName, $packageName = "" )
	{
		if( $packageName )
			$fileName	= $packageName."/".$fileName;
		$themePath	= $this->getBuilderPath();
		$fileUri	= $themePath."templates/".$fileName.".phpt";
		return $fileUri;
	}
	
	/**
	 *	Returns Path to Template.
	 *	@access		public
	 *	@return		string			Template Path
	 */
	public function getBuilderPath()
	{
		return 'builder/'.$this->config['project.builder.format'].'/'.$this->config['project.builder.theme']."/";
	}

	/**
	 *	Loads a Template and inserts Data.
	 *	@access		public
	 *	@param		string			$fileKey		Key of Template, e.g. folder.file.sub for themes/.../templates/folder/file.sub.html
	 *	@param		array			$data			Data Array to insert into Template
	 *	@return		string
	 */
	public function loadTemplate( $fileKey, $data )
	{
		if( !isset( $data['language'] ) )
			$data['language']	= $this->config['doc.language'];
		$fileUri	= $this->getFileNameFromTemplateKey( $fileKey );
		$template	= new UI_Template( $fileUri, $data );
		return $template->create();
	}
	
	protected function getFileNameFromTemplateKey( $fileKey )
	{
		$package	= "";
		$parts		= explode( ".", $fileKey );
		if( count( $parts ) > 1 )
			$package	= array_shift( $parts )."/";
		$fileKey	= implode( ".", $parts );
		$fileName	= $package.$fileKey.".html";
		$fileUri	= $this->getBuilderPath()."templates/".$fileName;
		return $fileUri;
	}

	public function hasTemplate( $fileKey )
	{
		$fileUri	= $this->getFileNameFromTemplateKey( $fileKey );
		return file_exists( $fileUri );
	}

	private function readClassIndex()
	{
		foreach( $this->data->getFiles() as $fileName => $file )
		{
			foreach( $file->getClasses() as $className => $class )
			{
				$category	= $class->getCategory() ? $class->getCategory() : 'default';
				$package	= $class->getPackage() ? $class->getPackage() : 'default';
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
		$this->tree	= new Model_Category( "root" );
		foreach( $this->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				if( !$class->getCategory() )
					continue;
				if( !$this->tree->hasPackage( $class->getCategory() ) )
					$this->tree->setPackage( $class->getCategory(), new Model_Category( $class->getCategory() ) );
				if( !$class->getPackage() )
					continue;
				$category	= $this->tree->getPackage( $class->getCategory() );
				$name		= str_replace( ".", "_", $class->getPackage() );
				$name		= array_pop( explode( "_", $name ) );
				$package	= new Model_Package( $this->capitalizePackageLabel( $name ) );
				$package->setClass( $class->getName(), $class );
				$category->setPackage( $class->getPackage(), $package );
			}
		}
	}
}
?>