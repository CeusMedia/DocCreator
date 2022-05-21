<?php
/**
 *	Builds Home Info Site File.
 *
 *	Copyright (c) 2008-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;

/**
 *	Builds Home Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Home extends SiteInfoAbstraction
{
	protected $key			= 'home';
	protected $fileNames	= array(
		'home',
		'home.txt',
		'home.html',
		'home.md',
		'readme.md',
		'README.md',
		'../readme.md',
		'../README.md',
	);

	/**
	 *	Creates History Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		if( !parent::createSiteByFile() ){
			$words	= isset( $this->env->words[$this->key] ) ? $this->env->words[$this->key] : array();
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'version'	=> $this->env->builder->title->getAttribute("version"),
				'words'		=> $words,
				'key'		=> $this->key,
				'id'		=> 'info-'.$this->key,
				'content'	=> "",
				'date'		=> date( $words['formatDate'], time() ),
				'time'		=> date( $words['formatTime'], time() ),
				'topic'		=> isset( $words['heading'] ) ? $words['heading'] : $this->key,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/'.$this->key;
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( $this->key.".html", $content );
			$this->appendLink( $this->key.".html", $this->key, 1, $this->key );
		}
		return TRUE;
	}
}
