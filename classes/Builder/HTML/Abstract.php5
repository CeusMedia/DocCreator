<?php
/**
 *	General Builder Class with useful Methods for inheriting Classes.
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
 *	@version		$Id: Abstract.php5 86 2012-05-23 12:18:48Z christian.wuerker $
 */
/**
 *	General Builder Class with useful Methods for inheriting Classes.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML
 *	@uses			UI_HTML_Elements
 *	@uses			UI_Template
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Abstract.php5 86 2012-05-23 12:18:48Z christian.wuerker $
 */
abstract class DocCreator_Builder_HTML_Abstract{

	/**	@var		DocCreator_Core_Environment		$env 		Environment Object */
	protected $env;
	/**	@var		string			$type			Data Type (class|file) */
	protected $type					= NULL;
	/**	@var		array			$words			Array of Language Pairs */
	protected $words;
	
	protected $cacheTemplate		= array();
	
	protected $pathTheme			= NULL;

	protected $pathBuilder			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env 		Environment Object
	 *	@param		string			$type		Data Type (class|file)
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Environment $env, $type = NULL ){
		$this->env			= $env;
		$this->type			= $type;
		$this->words		= $this->env->words;
		$this->pathBuilder	= dirname( dirname( __FILE__ ) ).'/';
		$this->pathTheme	= $this->getThemePath();
	}

	/**
	 *	Builds localized Access Label with Acronym if possible.
	 *	@access		protected
	 *	@param		string		$access		Access type
	 *	@return		string
	 */
	protected function buildAccessLabel( $access ){
		$label	= $access;
		if( array_key_exists( $access, $this->words['access'] ) ){
			$label	= $this->words['access'][$access];
			if( array_key_exists( $access, $this->words['accessAcronym'] ) )
				$label	= UI_HTML_Elements::Acronym( $label, $this->words['accessAcronym'][$access] );
		}
		return $label;
	}

	/**
	 *	Builds Category link if Category is resolvable, returns Category name otherwise.
	 *	@access		protected
	 *	@param		string		$categoryName		Category name
	 *	@return		string
	 */
	protected function buildCategoryLink( $categoryName ){
		foreach( $this->env->tree->getPackages() as $category ){
			if( get_class( $category ) == "ADT_PHP_Category" ){
				if( $category->getLabel() == $categoryName ){
					$url	= 'category.'.$categoryName.'.html';
					return UI_HTML_Elements::Link( $url, $categoryName, 'category' );
				}
			}
		}
		return $categoryName;
	}

	protected function buildFooter(){
		$data	= $this->env->tool;
		$data['date']	= date( "Y-m-d H:i" );
		return $this->loadTemplate( 'footer', $data );
	}

	/**
	 *	Builds Package link if Package is resolvable, returns Package name otherwise.
	 *	@access		protected
	 *	@param		string		$packageName		Package name
	 *	@param		string		$categoryName		Category name needed for resolution
	 *	@return		string
	 */
	protected function buildPackageLink( $packageName, $categoryName ){
		$package		= $this->getPackageFromName( $packageName, $categoryName );
		if( $package ){
			$packageUrl		= $this->getUrlFromPackage( $package );
			$packageName	= UI_HTML_Elements::Link( $packageUrl, $packageName, 'package' );
		}
		return $packageName;
	}

	/**
	 *	Builds Authors Entry for Parameters List.
	 *	@access		protected
	 *	@param		array			$data		Authors Data Array
	 *	@param		array			$list		Result List of Authors, empty but can be preset
	 *	@return		string
	 */
	protected function buildParamAuthors( $data, $list = array() ){
		foreach( $data->getAuthors() as $author ){
			if( $author->getEmail() )
				$author	= UI_HTML_Elements::Link( "mailto:".$author->getEmail(), $author->getName(), $this->type.'-info-author' );
			else
				$author	= $author->getName();
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $author ) );
		}
		return $this->buildParamList( $list, 'authors' );
	}

	protected function buildParamLinkedList( $data, $key, $list = array() ){
		foreach( $data as $url ){
			$link	= UI_HTML_Elements::Link( $url, $url, $this->type.'-info-link' );
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $link ) );
		}
		return $this->buildParamList( $list, $key );
	}
	
	protected function buildParamList( $list, $title ){
		$type	= 'param'.ucFirst( $title );
		if( !$list )
			return "";
		if( is_array( $list ) ){
			$data	= array(
				'list'					=> implode( "\n", $list ),
				(string) $this->type	=> $type
			);
			$list	= $this->loadTemplate( $this->type.'.info.param.list', $data );
		}
		$data	= array(
			'key'	=> $this->words[$this->type.'Info'][$type],
			'value'	=> $list,
			'class'	=> $title,
		);
		return $this->loadTemplate( $this->type.'.info.param', $data );
	}

	protected function buildParamStringList( $value, $key, $list = array() ){
		if( !is_array( $value ) )
			return $this->buildParamList( $value, $key );
		foreach( $value as $label ){
			$label	= $this->realizeInlineLinks( $label );
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $label ) );
		}
		return $this->buildParamList( $list, $key );
	}

	/**
	 *	Returns full Template File URI from Template File Key.
	 *	@access		protected
	 *	@param		string			$fileKey		Template File Key, eg. 'folder.folder.basename'
	 *	@return		string
	 */
	protected function getFileNameFromTemplateKey( $fileKey ){
		$package		= "";
		$fileKeyParts	= explode( ".", $fileKey );
		if( count( $fileKeyParts ) > 1 )
			$package	= array_shift( $fileKeyParts )."/";
		$fileKey		= implode( ".", $fileKeyParts );
		$fileName		= $package.$fileKey.".html";
		$templateUri	= $this->pathTheme.'templates/'.$fileName;
		return $templateUri;
	}

	protected function getFormatedDescription( $description ){
		$description	= trim( (string) $description );
		$description	= htmlentities( $description, ENT_QUOTES, 'UTF-8' );
		$description	= nl2br( $description );
		$description	= $this->realizeInlineLinks( $description );
		return $description;
	}

	/**
	 *	Returns Package Object from Package Name of resolvable, needs Category Name for resolution.
	 *	@access		protected
	 *	@param		string				$packageName		Name of Package to get
	 *	@param		string				$categoryName		Name of Category for resolution
	 *	@return		ADT_PHP_Package|NULL
	 */
	protected function getPackageFromName( $packageName, $categoryName ){
		$packageId	= $categoryName.'-'.$packageName;
		if( array_key_exists( $packageId, $this->env->packageList ) )
			return $this->env->packageList[$packageId];
		return NULL;
	}

	protected function getParameterMarkUp( ADT_PHP_Parameter $data ){
		$name		= $data->getName();
		$name		= $data->isReference() ? "&amp;&nbsp;$".$name : "$".$name;
		$name		= $data->getDefault() ? '<small>['.$name.']</small>' : $name;
		if( $data->getDescription() )
			$name	= UI_HTML_Elements::Acronym( $name, $data->getDescription() );

		$type		= $data->getCast() ? $data->getCast() : ( $data->getType() ? $data->getType() : "unknown" );
		$type		= $this->getTypeMarkUp( $type );
		$default	= $data->getDefault() ? '<span class="default"> = '.$data->getDefault().'</span>' : "";
		$code		= $type.' '.$name.$default;
		return $code;
	}

	/**
	 *	Returns full Path to Builder Theme.
	 *	@access		protected
	 *	@return		string
	 */
	protected function getThemePath(){
		$themeKey		= $this->env->getBuilderTheme();
		$themePath		= $this->env->path.'themes/'.$themeKey.'/';
		return $themePath;
	}
	
	protected function getTypeMarkUp( $type ){
#		if( is_object( $type ) )
#			remark( $type->getName()." - ".get_class( $type ) );
		if( !$type )
			return "";
#			throw new Exception( 'Type cannot be empty' );
		$label	= $type;
		if( is_object( $type ) ){
			switch( get_class( $type ) ){
				case 'ADT_PHP_Package':
					$url	= $this->getUrlFromPackage( $type );
					$label	= UI_HTML_Elements::Link( $url, $type->getLabel(), 'package' );
					break;
				case 'ADT_PHP_Class':
					$url	= $this->getUrlFromClass( $type );
					$label	= UI_HTML_Elements::Link( $url, $type->getName(), 'class' );
					break;
				case 'ADT_PHP_Interface':
					$url	= $this->getUrlFromInterface( $type );
					$label	= UI_HTML_Elements::Link( $url, $type->getName(), 'interface' );
					break;
				default:
					throw new Exception( 'Invalid type' );
			}
		}
		else if( is_string( $type ) ){
			if( in_array( $type, $this->env->phpClasses ) ){
				$url	= "http://us3.php.net/manual/en/class.".strtolower( $type ).".php";
				$label	= UI_HTML_Elements::Link( $url, "PHP: ".$type );
			}
			else if( array_key_exists( $type, $this->env->words['types'] ) ){
				$url	= "http://us3.php.net/manual/en/language.types.".strtolower( $type ).".php";
				$label	= UI_HTML_Elements::Link( $url, $label );
				$label	= UI_HTML_Elements::Acronym( $label, $this->env->words['types'][$type] );
			}
			else if( array_key_exists( $type, $this->env->words['pseudoTypes'] ) ){
				$url	= "http://us3.php.net/manual/en/language.pseudo-types.php#language.types.".strtolower( $type );
				if( $type == "dotdotdot" )
					$label	= "...";
				$label	= UI_HTML_Elements::Link( $url, $label );
				$label	= UI_HTML_Elements::Acronym( $label, $this->env->words['pseudoTypes'][$type] );
			}
			else if( $type == "unknown" )
				return "";
#				$label	= UI_HTML_Tag::create( 'small', $type );
#			else if( $type !== "unknown" )
#				remark( "!getTypeMarkUp: ".$type );
		}
		return UI_HTML_Tag::create( 'span', $label, array( 'class' => 'type' ) );
	}

	/**
	 *	Returns the Doc URL from a Class Data Object.
	 *	@static
	 *	@access		protected
	 *	@param		ADT_PHP_Class		$class			Class Object to get Doc URL for
	 *	@return		string
	 */
	protected static function getUrlFromClass( ADT_PHP_Class $class ){
		return "class.".$class->getId().".html";
	}

	/**
	 *	Returns Doc URL for a Class Name if within indexed Classes.
	 *	@access		protected
	 *	@param		string				$className		Name of Class to get Doc URL for
	 *	@param		ADT_PHP_Interface	$relatedClass	Class Object related to Class to find
	 *	@return		ADT_PHP_Class
	 */
	protected function getUrlFromClassName( $className, ADT_PHP_Interface $relatedClass ){
		try{
			$class	= $this->env->data->getClassFromClassName( $className, $relatedClass );
			return $this->getUrlFromClass( $class );
		}
		catch( Exception $e ){
			remark( "Builder::getUrlFromClassName: ".$e->getMessage() );
			return "";
		}
	}

	/**
	 *	Returns the Doc URL from a Interface Data Object.
	 *	@access		protected
	 *	@param		ADT_PHP_Interface	$interface		Interface Object to get Doc URL for
	 *	@return		string
	 */
	protected function getUrlFromInterface( ADT_PHP_Interface $interface ){
		$interfaceId	= $interface->getId();
		$url	= "interface.".$interfaceId.".html";
		return $url;
	}

	/**
	 *	Returns Doc URL for an Interface Name if within indexed Interfaces.
	 *	@access		protected
	 *	@param		string				$interfaceName		Name of Interface to get Doc URL for
	 *	@param		ADT_PHP_Interface	$relatedArtefact	Class or Interface Object related to Interface to find
	 *	@return		ADT_PHP_Interface
	 */
	protected function getUrlFromInterfaceName( $interfaceName, ADT_PHP_Interface $relatedArtefact ){
		try{
			$interface	= $this->env->data->getInterfaceFromInterfaceName( $interfaceName, $relatedArtefact );
			return $this->getUrlFromInterface( $interface );
		}
		catch( Exception $e ){
			remark( "Builder::getUrlFromInterfaceName: ".$e->getMessage() );
			return "";
		}
	}

	/**
	 *	Returns Doc URL from Package Object.
	 *	@static
	 *	@access		protected
	 *	@param		ADT_PHP_Package	$package		Package Data Object
	 *	@return		string
	 */
	protected static function getUrlFromPackage( ADT_PHP_Package $package ){
		return "package.".$package->getId().".html";
	}

	/**
	 *	Indicates whether a Template is existing by its File Key.
	 *	@access		protected
	 *	@param		string			$fileKey		Template File Key, eg. 'folder.folder.basename'
	 *	@return		bool
	 */
	protected function hasTemplate( $fileKey ){
		return file_exists( $this->getFileNameFromTemplateKey( $fileKey ) );
	}

	/**
	 *	Loads a Template and inserts Data.
	 *	@access		protected
	 *	@param		string			$fileKey		Key of Template, e.g. folder.file.sub for themes/.../templates/folder/file.sub.html
	 *	@param		array			$data			Data Array to insert into Template
	 *	@return		string
	 */
	protected function loadTemplate( $fileKey, $data ){
		if( !isset( $data['language'] ) )
			$data['language']	= $this->env->builder->language->getValue();
		if( !isset( $data['theme'] ) )
			$data['theme']	= $this->env->builder->getAttribute( 'theme' );
		$fileHash	= md5( $fileKey );
		if( array_key_exists( $fileHash, $this->cacheTemplate ) )
			$content	= $this->cacheTemplate[$fileHash];
		else{
			$fileUri	= $this->getFileNameFromTemplateKey( $fileKey );
			$content	= file_get_contents( $fileUri );
			$this->cacheTemplate[$fileHash]	= $content;
		}
		return UI_Template::renderString( $content, $data );
	}

	protected function realizeInlineLinks( $string ){
		$string	= preg_replace( "/\{@link (\S+) (\S+)\}/U", '<a href="\\1">\\2</a>', $string );
		$string	= preg_replace( "/\{@link (\S+)\}/U", '<a href="\\1">\\1</a>', $string );
		return $string;
	}

	public static function removeFiles( $path, $pattern ){
		$index	= new File_RecursiveRegexFilter( $path, $pattern );									// index formerly generated or copied files
		foreach( $index as $entry )																	// iterate index
			@unlink( $entry->getPathname());														// remove outdated files
	}
}
?>
