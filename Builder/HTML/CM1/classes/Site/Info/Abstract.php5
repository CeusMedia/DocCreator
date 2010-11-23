<?php
/**
 *	Abstract Site Info Builder.
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
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
import( 'de.ceus-media.file.Writer' );
import( 'builder.html.cm1.classes.Abstract' );
/**
 *	Abstract Site Info Builder.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Abstract
 *	@uses			File_Writer
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
abstract class Builder_HTML_CM1_Site_Info_Abstract extends Builder_HTML_CM1_Abstract
{
	protected $pathProject	= NULL;
	protected $pathTarget	= NULL;
	protected $linkList		= NULL;
	protected $linkTarget	= 'content';
	protected $fileNames	= array();
	protected $key			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Environment	$env		Environment Object
	 *	@param		array						$linkList	Reference to list of Site links
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Environment $env, &$linkList )
	{
		parent::__construct( $env );
		$this->linkList	=& $linkList;
	}
	
	protected function appendLink( $url, $key, $count = NULL, $class = NULL )
	{
		$this->linkList[]	= array(
			'url'	=> $url,
			'key'	=> $key,
			'count'	=> $count,
			'class'	=> $class,
		);
	}

	abstract public function createSite();

	/**
	 *	Creates About Site if any About File is available in Project Folder.
	 *	@access		public
	 *	@param		string			$pathProject	Path to Project Configuration
	 *	@param		string			$pathTarget		Path to save Sites in
	 *	@throws		Exception		if unsufficient data
	 *	@return		bool
	 */
	public function createSiteByFile()
	{
		if( !$this->fileNames )
			throw new Exception( 'No files set' );
		if( !$this->key )
			throw new Exception( 'No key set' );

		$list		= array();
		$pathDocs	= $this->env->getBuilderDocumentsPath();
		if( !$pathDocs )
			return;
		foreach( $this->fileNames as $fileName )
		{
			$fileName	= $pathDocs.$fileName;
			if( file_exists( $fileName ) )
			{
				$header		= '<div class="file-uri">'.$fileName.'</div>';
				$content	= '<pre class="text">'.File_Reader::load( $fileName ).'</xmp>';
				$list[]		= $header.$content;
			}
		}
		if( $list )
		{
			$this->verboseCreation( $this->key );
			$words	= isset( $this->env->words[$this->key] ) ? $this->env->words[$this->key] : array();
			$uiData	= array(
				'words'		=> $words,
				'key'		=> $this->key,
				'id'		=> 'info-'.$this->key,
				'content'	=> implode( "\n\n", $list ),
				'title'		=> isset( $words['heading'] ) ? $words['heading'] : $this->key,
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
	
	protected function saveFile( $fileName, $content )
	{
		File_Writer::save( $this->pathTarget.$fileName, $content );
	}

	public function setLinkTargetFrame( $linkTarget )
	{
		$this->linkTarget	= $linkTarget;
	}

	public function setProjectPath( $pathProject )
	{
		$this->pathProject	= $pathProject;
	}

	public function setTargetPath( $pathTarget )
	{
		$this->pathTarget	= $pathTarget;
	}
	
	public function verboseCreation( $key )
	{
		if( !$this->env->verbose )
			return;
		$words	= $this->env->words['links'];
		$label	= isset( $words[$key] ) ? $words[$key] : $key;
		remark( "Creating Info Site: ".$label );
	}
}
?>