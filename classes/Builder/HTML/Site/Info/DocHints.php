<?php
/**
 *	...
 *
 *	Copyright (c) 2008-2015 Christian Würker (ceusmedia.de)
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
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: DocHints.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
/**
 *	Builds Deprecation Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@uses			Alg_UnusedVariableFinder
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: DocHints.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_DocHints extends DocCreator_Builder_HTML_Site_Info_Abstract
{
	protected $key			= 'docHints';
	protected $count		= 0;
	protected $points		= array(
		'class.description.missing'	=> 5,
		'class.category.missing'	=> 5,
		'class.package.missing'		=> 5,
		'class.author.missing'		=> 5,
		'class.version.missing'		=> 5,
	);
	public $checkClassVersion	= FALSE;
	public $checkClassLicense	= FALSE;

	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		$content	= "";
		$list		= array();
		$words		= isset( $this->words[$this->key] ) ? $this->words[$this->key] : array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$classNotes	= array();
				if( !preg_match( '/^.{20,}$/s', $class->getDescription() ) )
					$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.description.missing'] );
				if( !preg_match( '/^\S+$/', $class->getCategory() ) )
					$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.category.missing'] );
				if( !preg_match( '/^\S+$/', $class->getPackage() ) )
					$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.package.missing'] );
				if( $this->checkClassVersion )
					if( !preg_match( '/^.{4,}$/s', $class->getVersion() ) )
						$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.version.missing'] );

				$authors	= $class->getAuthors();
				if( !$authors )
					$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.author.missing'] );
				else
					foreach( $authors as $author )
						if( !$author->getEmail() )
							$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.author.email.missing'] );

				if( $this->checkClassLicense ){
					$licenses	= $class->getLicenses();
					if( !$licenses )
						$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.license.missing'] );
					else
						foreach( $licenses as $license )
							if( !$license->getUrl() )
								$classNotes[]	= UI_HTML_Elements::ListItem( $words['class.license.url.missing'] );
				}

				foreach( $class->getMethods() as $method )
				{
					$methodNotes	= array();
					if( !preg_match( '/^\w+\s+\w+$/', $method->getDescription() ) )
						$methodNotes[]	= UI_HTML_Elements::ListItem( $words['method.description.missing'] );
					if( !$method->getReturn() )
						$methodNotes[]	= UI_HTML_Elements::ListItem( $words['method.return.missing'] );
					if( $methodNotes )
					{
						$methodNotes	= UI_HTML_Elements::unorderedList( $methodNotes, 2 );
						$link			= UI_HTML_Elements::Link( 'class.'.$class->getId().".html#class_method_".$method->getName(), $method->getName(), 'method' );
						$classNotes[]	= UI_HTML_Elements::ListItem( $link.$methodNotes, 2, array( 'class' => 'method' ) );
					}
				}
	
				if( !$classNotes )
					continue;

					$this->count++;
				
				$notes	= UI_HTML_Elements::unorderedList( $classNotes );

				$link	= UI_HTML_Elements::Link( 'class.'.$class->getId().'.html', $class->getName(), 'class' );
				$count	= ' <small>('.count( $notes ).')</small>';
				$item	= UI_HTML_Elements::ListItem( $link.$count.$notes, 0, array( 'class' => 'class' ) );
				$list[]	= $item;
			}
		}
		if( $this->count )
		{
			$this->verboseCreation( $this->key );

			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> $this->key,
				'id'		=> 'info-'.$this->key,
				'topic'		=> isset( $words['heading'] ) ? $words['heading'] : $this->key,
				'content'	=> '<div id="tree">'.UI_HTML_Elements::unorderedList( $list ).'</div>',
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/'.$this->key;
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( $this->key.'.html', $content );
			$this->appendLink( $this->key.'.html', $this->key, $this->count );
		}
		return (bool) $this->count;
	}
}
?>
