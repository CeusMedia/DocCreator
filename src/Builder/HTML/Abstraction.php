<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	General Builder Class with useful Methods for inheriting Classes.
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

use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveFileRegexFilter;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\TemplateEngine\Template as TemplateEngine;
use CeusMedia\DocCreator\Core\Environment;
use CeusMedia\PhpParser\Structure\Category_ as PhpCategory;
use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;
use CeusMedia\PhpParser\Structure\Package_ as PhpPackage;
use CeusMedia\PhpParser\Structure\Parameter_ as PhpParameter;
use CeusMedia\PhpParser\Structure\Function_ as PhpFunction;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;
use Exception;
use RuntimeException;

/**
 *	General Builder Class with useful Methods for inheriting Classes.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
abstract class Abstraction
{
	public const FILE_PERMS		= 0664;

	/**	@var		Environment		$env 			Environment Object */
	protected Environment $env;
	/**	@var		string|NULL		$type			Data Type (class|file) */
	protected ?string $type			= NULL;
	/**	@var		array			$words			Array of Language Pairs */
	protected array $words;

	protected array $cacheTemplate	= [];

	protected string $pathTheme;

	protected string $pathBuilder;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env 		Environment Object
	 *	@param		string|NULL		$type		Data Type (class|file)
	 *	@return		void
	 */
	public function __construct( Environment $env, string $type = NULL )
	{
		$this->env			= $env;
		$this->type			= $type;
		$this->words		= $this->env->words;
		$this->pathBuilder	= dirname( __FILE__, 2 ) .'/';
		$this->pathTheme	= $this->getThemePath();
	}

	public static function removeFiles( string $path, string $pattern ): void
	{
		$index	= new RecursiveFileRegexFilter( $path, $pattern );									// index formerly generated or copied files
		foreach( $index as $entry )																	// iterate index
			@unlink( $entry->getPathname());														// remove outdated files
	}

	/**
	 *	Returns the Doc URL from a Class Data Object.
	 *	@static
	 *	@access		protected
	 *	@param		PhpClass		$class			Class Object to get Doc URL for
	 *	@return		string
	 */
	protected static function getUrlFromClass( PhpClass $class ): string
	{
		return "class.".$class->getId().".html";
	}

	/**
	 *	Returns Doc URL from Package Object.
	 *	@static
	 *	@access		protected
	 *	@param		PhpPackage		$package		Package Data Object
	 *	@return		string
	 */
	protected static function getUrlFromPackage( PhpPackage $package ): string
	{
		return "package.".$package->getId().".html";
	}

	/**
	 *	Builds localized Access Label with Acronym if possible.
	 *	@access		protected
	 *	@param		string		$access		Access type
	 *	@return		string
	 */
	protected function buildAccessLabel( string $access ): string
	{
		$label	= $access;
		if( array_key_exists( $access, $this->words['access'] ) ){
			$label	= $this->words['access'][$access];
			if( array_key_exists( $access, $this->words['accessAcronym'] ) )
				$label	= HtmlElements::Acronym( $label, $this->words['accessAcronym'][$access] );
		}
		return $label;
	}

	/**
	 *	Builds Category link if Category is resolvable, returns Category name otherwise.
	 *	@access		protected
	 *	@param		string		$categoryName		Category name
	 *	@return		string
	 */
	protected function buildCategoryLink( string $categoryName ): string
	{
		foreach( $this->env->tree->getPackages() as $category ){
			if( get_class( $category ) == PhpCategory::class ){
				if( $category->getLabel() == $categoryName ){
					$url	= 'category.'.$categoryName.'.html';
					return HtmlElements::Link( $url, $categoryName, 'category' );
				}
			}
		}
		return $categoryName;
	}

	protected function buildFooter(): string
	{
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
	protected function buildPackageLink( string $packageName, string $categoryName ): string
	{
		$package		= $this->getPackageFromName( $packageName, $categoryName );
		if( $package ){
			$packageUrl		= static::getUrlFromPackage( $package );
			$packageName	= HtmlElements::Link( $packageUrl, $packageName, 'package' );
		}
		return $packageName;
	}

	/**
	 *	Builds Authors Entry for Parameters List.
	 *	@access		protected
	 *	@param		PhpFunction|PhpFile	$data		Authors Data Array
	 *	@param		array				$list		Result List of Authors, empty but can be preset
	 *	@return		string
	 */
	protected function buildParamAuthors(  $data, array $list = [] ): string
	{
		foreach( $data->getAuthors() as $author ){
			if( $author->getEmail() )
				$author	= HtmlElements::Link( "mailto:".$author->getEmail(), $author->getName(), $this->type.'-info-author' );
			else
				$author	= $author->getName();
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', ['value' => $author] );
		}
		return $this->buildParamList( $list, 'authors' );
	}

	protected function buildParamLinkedList( array $data, string $key, array $list = [] ): string
	{
		foreach( $data as $url ){
			$link	= HtmlElements::Link( $url, $url, $this->type.'-info-link' );
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', ['value' => $link] );
		}
		return $this->buildParamList( $list, $key );
	}

	protected function buildParamList( $list, string $title ): string
	{
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
		$data	= [
			'key'	=> $this->words[$this->type.'Info'][$type],
			'value'	=> $list,
			'class'	=> $title,
		];
		return $this->loadTemplate( $this->type.'.info.param', $data );
	}

	protected function buildParamStringList( $value, string $key, array $list = [] ): string
	{
		if( !is_array( $value ) )
			return $this->buildParamList( $value, $key );
		foreach( $value as $label ){
			$label	= $this->realizeInlineLinks( $label );
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', ['value' => $label] );
		}
		return $this->buildParamList( $list, $key );
	}

	/**
	 *	Returns full Template File URI from Template File Key.
	 *	@access		protected
	 *	@param		string			$fileKey		Template File Key, eg. 'folder.folder.basename'
	 *	@return		string
	 */
	protected function getFileNameFromTemplateKey( string $fileKey ): string
	{
		$package		= "";
		$fileKeyParts	= explode( ".", $fileKey );
		if( count( $fileKeyParts ) > 1 )
			$package	= array_shift( $fileKeyParts )."/";
		$fileKey		= implode( ".", $fileKeyParts );
		$fileName		= $package.$fileKey.".html";
		$templateUri	= $this->pathTheme.'templates/'.$fileName;
		return $templateUri;
	}

	protected function getFormatedDescription( ?string $description = '' )
	{
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
	 *	@return		PhpPackage|NULL
	 */
	protected function getPackageFromName( string $packageName, string $categoryName ): ?PhpPackage
	{
		$packageId	= $categoryName.'-'.$packageName;
		if( array_key_exists( $packageId, $this->env->packageList ) )
			return $this->env->packageList[$packageId];
		return NULL;
	}

	protected function getParameterMarkUp( PhpParameter $data ): string
	{
		$name		= $data->getName();
		$name		= $data->isReference() ? "&amp;&nbsp;$".$name : "$".$name;
		$name		= $data->getDefault() ? '<small>['.$name.']</small>' : $name;
		if( $data->getDescription() )
			$name	= HtmlElements::Acronym( $name, $data->getDescription() );

		$type		= $data->getCast() ?: ( $data->getType() ?: "unknown" );
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
	protected function getThemePath(): string
	{
		return $this->env->getBuilderThemePath();
	}

	protected function getTypeMarkUp( $type ): string
	{
#		if( is_object( $type ) )
#			remark( $type->getName()." - ".get_class( $type ) );
		if( !$type )
			return "";
#			throw new Exception( 'Type cannot be empty' );
		$label	= $type;
		if( is_object( $type ) ){
			switch( get_class( $type ) ){
				case PhpPackage::class:
					$url	= static::getUrlFromPackage( $type );
					$label	= HtmlElements::Link( $url, $type->getLabel(), 'package' );
					break;
				case PhpClass::class:
					$url	= static::getUrlFromClass( $type );
					$label	= HtmlElements::Link( $url, $type->getName(), 'class' );
					break;
				case PhpInterface::class:
					$url	= $this->getUrlFromInterface( $type );
					$label	= HtmlElements::Link( $url, $type->getName(), 'interface' );
					break;
				default:
					throw new RuntimeException( 'Invalid type' );
			}
		}
		else if( is_string( $type ) ){
			if( in_array( $type, $this->env->phpClasses ) ){
				$url	= "http://us3.php.net/manual/en/class.".strtolower( $type ).".php";
				$label	= HtmlElements::Link( $url, "PHP: ".$type );
			}
			else if( array_key_exists( $type, $this->env->words['types'] ) ){
				$url	= "http://us3.php.net/manual/en/language.types.".strtolower( $type ).".php";
				$label	= HtmlElements::Link( $url, $label );
				$label	= HtmlElements::Acronym( $label, $this->env->words['types'][$type] );
			}
			else if( array_key_exists( $type, $this->env->words['pseudoTypes'] ) ){
				$url	= "http://us3.php.net/manual/en/language.pseudo-types.php#language.types.".strtolower( $type );
				if( $type == "dotdotdot" )
					$label	= "...";
				$label	= HtmlElements::Link( $url, $label );
				$label	= HtmlElements::Acronym( $label, $this->env->words['pseudoTypes'][$type] );
			}
			else if( $type == "unknown" )
				return "";
#				$label	= HtmlTag::create( 'small', $type );
#			else if( $type !== "unknown" )
#				remark( "!getTypeMarkUp: ".$type );
		}
		return HtmlTag::create( 'span', $label, ['class' => 'type'] );
	}

	/**
	 *	Returns Doc URL for a Class Name if within indexed Classes.
	 *	@access		protected
	 *	@param		string			$className		Name of Class to get Doc URL for
	 *	@param		PhpInterface	$relatedClass	Class Object related to Class to find
	 *	@return		PhpClass
	 */
	protected function getUrlFromClassName( string $className, PhpInterface $relatedClass ): PhpClass
	{
		try{
			$class	= $this->env->data->getClassFromClassName( $className, $relatedClass );
			return static::getUrlFromClass( $class );
		}
		catch( \Exception $e ){
			remark( "Builder::getUrlFromClassName: ".$e->getMessage() );
			return "";
		}
	}

	/**
	 *	Returns the Doc URL from a Interface Data Object.
	 *	@access		protected
	 *	@param		PhpInterface	$interface		Interface Object to get Doc URL for
	 *	@return		string
	 */
	protected function getUrlFromInterface( PhpInterface $interface ): string
	{
		$interfaceId	= $interface->getId();
		$url	= "interface.".$interfaceId.".html";
		return $url;
	}

	/**
	 *	Returns Doc URL for an Interface Name if within indexed Interfaces.
	 *	@access		protected
	 *	@param		string			$interfaceName		Name of Interface to get Doc URL for
	 *	@param		PhpInterface	$relatedArtefact	Class or Interface Object related to Interface to find
	 *	@return		PhpInterface
	 */
	protected function getUrlFromInterfaceName( string $interfaceName, PhpInterface $relatedArtefact )
	{
		try{
			$interface	= $this->env->data->getInterfaceFromInterfaceName( $interfaceName, $relatedArtefact );
			return $this->getUrlFromInterface( $interface );
		}
		catch( \Exception $e ){
			remark( "Builder::getUrlFromInterfaceName: ".$e->getMessage() );
			return "";
		}
	}

	/**
	 *	Indicates whether a Template is existing by its File Key.
	 *	@access		protected
	 *	@param		string			$fileKey		Template File Key, eg. 'folder.folder.basename'
	 *	@return		bool
	 */
	protected function hasTemplate( string $fileKey ): bool
	{
		return file_exists( $this->getFileNameFromTemplateKey( $fileKey ) );
	}

	/**
	 *	Loads a Template and inserts Data.
	 *	@access		protected
	 *	@param		string			$fileKey		Key of Template, e.g. folder.file.sub for themes/.../templates/folder/file.sub.html
	 *	@param		array			$data			Data Array to insert into Template
	 *	@return		string
	 */
	protected function loadTemplate( string $fileKey, array $data ): string
	{
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
		return TemplateEngine::renderString( $content, $data );
	}

	protected function realizeInlineLinks( string $string ): string
	{
		$string	= preg_replace( "/\{@link (\S+) (\S+)\}/U", '<a href="\\1">\\2</a>', $string );
		$string	= preg_replace( "/\{@link (\S+)\}/U", '<a href="\\1">\\1</a>', $string );
		return $string;
	}
}
