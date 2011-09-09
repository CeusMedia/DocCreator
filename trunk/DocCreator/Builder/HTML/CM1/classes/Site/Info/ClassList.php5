<?php
/**
 *	Builds Class List Info Site File.
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
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
import( 'builder.html.cm1.classes.site.info.Abstract' );
/**
 *	Builds Class List Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
class Builder_HTML_CM1_Site_Info_ClassList extends Builder_HTML_CM1_Site_Info_Abstract
{
	public $linkTarget = '_self';

	private function getLabel( $artefact ){
		if( !empty( $this->options['prefix'] ) )
			return preg_replace( '/'.$this->options['prefix'].'/', '', $artefact->getName() );
		return $artefact->getName();
	}

	private function buildClassList()
	{
		$divClear	= UI_HTML_Tag::create( 'div', '', array( 'style' => 'clear: both' ) );

		$list		= array();
		foreach( $this->env->data->getFiles() as $fileId=> $file )
		{
			foreach( $file->getClasses() as $classId => $class )
			{
				$uri	= 'class.'.$class->getId().'.html';
				$label	= $this->getLabel( $class );
				$link	= UI_HTML_Elements::Link( $uri, $label, 'class', $this->linkTarget );
				$type	= $this->getTypeMarkUp( $link );
				$div	= UI_HTML_Tag::create( 'div', $link/*type*/, array( 'class' => 'class' ) );
				$list[$label.time()]	= $div;
			}
			foreach( $file->getInterfaces() as $interfaceId => $interface )
			{
				$uri	= 'interface.'.$interface->getId().'.html';
				$label	= $this->getLabel( $interface );
				$link	= UI_HTML_Elements::Link( $uri, $label, 'interface', $this->linkTarget );
				$type	= $this->getTypeMarkUp( $link );
				$div	= UI_HTML_Tag::create( 'div', $link/*type*/, array( 'class' => 'interface' ) );
				$list[$label.time()]	= $div;
			}
		}
		ksort( $list );
		$last		= "";
		$letters	= array();
		$lines		= array();
		foreach( $list as $key => $item )
		{
			if( $last != $key[0] )
			{
				$letters[]	= $key[0];
				$divLetter	= UI_HTML_Tag::create(
					'div',
					$key[0],
					array(
						'class'	=> 'letter',
						'id'	=> 'letter-'.$key[0]
					)
				); 
				$lines[]	= $divClear.$divLetter;
			}
			$lines[]	= $item;
			$last		= $key[0];
		}
		
		$list	= array();
		for( $i=65; $i<91; $i++ )
		{
			$letter	= chr( $i );
			if( in_array( $letter, $letters ) )
				$item	= UI_HTML_Elements::Link( '#letter-'.$letter, $letter );
			else
				$item	= UI_HTML_Tag::create( 'span', $letter, array( 'class' => 'letter-disabled' ) );
			$list[]	= $item.'&nbsp;';
		}
		$list		= implode( $list );
		$letters	= UI_HTML_Tag::create( 'div', $list, array( 'id' => 'list-letters' ) );

		$list		= implode( "\n", $lines ).$divClear;
		return $letters.$list;
	}
	
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function createSite()
	{
		if( $this->env->verbose )
			remark( "Creating Site: Class List" );
		$uiData	= array(
			'words'		=> $this->env->words['classList'],
			'list'		=> $this->buildClassList(),
			'footer'	=> $this->buildFooter(),
		);
		$content	= $this->loadTemplate( "site/info/classList", $uiData );
		$this->saveFile( "classes.html", $content );
		$this->appendLink( 'classes.html', 'classList' );
	}
}
?>