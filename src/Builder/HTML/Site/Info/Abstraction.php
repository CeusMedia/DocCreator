<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Abstract Site Info Builder.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\DocCreator\Core\Environment;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;
use RuntimeException;

/**
 *	Abstract Site Info Builder.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
abstract class Abstraction extends HtmlBuilderAbstraction
{
	protected $pathProject	= NULL;
	protected $pathTarget	= NULL;
	protected $linkList		= NULL;
	protected string $linkTarget	= 'content';
	protected array $fileNames		= [];
	protected string $key			= '';
	protected array $options		= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment Object
	 *	@param		array			$linkList	Reference to list of Site links
	 *	@param		array			$options	...
	 *	@return		void
	 */
	public function __construct( Environment $env, string $type = NULL, array &$linkList, array $options = [] )
	{
		parent::__construct( $env, $type );
		$this->linkList	=& $linkList;
		$this->options	= $options;
	}

	abstract public function createSite();

	/**
	 *	Creates site if any file (from ::$fileNames) has been found in product documentation folder.
	 *	@access		public
	 *	@return		integer		Number of found and enlisted contents (files)
	 *	@throws		RuntimeException
	 *	@throws		CommonMarkException
	 */
	public function createSiteByFile(): int
	{
		if( !$this->fileNames )
			throw new RuntimeException( 'No files set' );
		if( !$this->key )
			throw new RuntimeException( 'No key set' );

		$list		= [];
		$pathDocs	= $this->env->getBuilderDocumentsPath();
		if( !$pathDocs )
			return 0;
		foreach( $this->fileNames as $fileName ){
			$fileName	= $pathDocs.$fileName;
			if( file_exists( $fileName ) ){
				$header		= '<div class="file-uri">'.$fileName.'</div>';
				$content	= FileReader::load( $fileName );
				$extension	= pathinfo( $fileName, PATHINFO_EXTENSION );
				switch( $extension ){
					case 'md':
//						$content	= \Michelf\Markdown::defaultTransform( $content );
						$converter	= new CommonMarkConverter();
						$content	= $converter->convertToHtml( $content );
						break;
					case 'html':
					case 'htm':
						break;
					default:
						$content	= '<pre class="text">'.$content.'</pre>';
				}
				$list[]		= $header.$content;
			}
		}
		if( $list ){
			$this->verboseCreation( $this->key );
			$words	= $this->env->words[$this->key] ?? [];
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'words'		=> $words,
				'key'		=> $this->key,
				'id'		=> 'info-'.$this->key,
				'content'	=> implode( "\n\n", $list ),
				'topic'		=> $words['heading'] ?? $this->key,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/'.$this->key;
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( $this->key.".html", $content );
			$this->appendLink( $this->key.".html", $this->key, count( $list ), $this->key );
		}
		return count( $list );
	}

	public function setLinkTargetFrame( string $linkTarget ): self
	{
		$this->linkTarget	= $linkTarget;
		return $this;
	}

	public function setProjectPath( string $pathProject ): self
	{
		$this->pathProject	= $pathProject;
		return $this;
	}

	public function setTargetPath( string $pathTarget ): self
	{
		$this->pathTarget	= $pathTarget;
		return $this;
	}

	public function verboseCreation( string $key )
	{
		if( !$this->env->verbose )
			return;
		$words	= $this->env->words['links'];
		$label	= $words[$key] ?? $key;
		$this->env->out->newLine( "Creating site: ".$label );
	}

	protected function appendLink( $url, $key, $count = NULL, $class = NULL )
	{
		$this->linkList[]	= [
			'url'	=> $url,
			'key'	=> $key,
			'count'	=> $count,
			'class'	=> $class,
		];
	}

	protected function saveFile( $fileName, $content )
	{
        FileWriter::save( $this->pathTarget.$fileName, $content, self::FILE_PERMS );
	}
}
