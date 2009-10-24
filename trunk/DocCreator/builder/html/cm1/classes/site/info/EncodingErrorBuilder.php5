<?php
/**
 *	Builds Encoding Error Info Site File.
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
 *	@version		$Id: EncodingErrorBuilder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Abstract' );
/**
 *	Builds Encoding Error Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: EncodingErrorBuilder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 *	@todo			Code Doc
 */
class Builder_HTML_CM1_Site_Info_EncodingErrorBuilder extends Builder_HTML_CM1_Abstract
{
	/**
	 *	Creates Change Log Sites if any Change Log File is available in Project Folder.
	 *	@access		public
	 *	@param		string			$pathTarget		Path to save Sites in
	 *	@return		bool
	 */
	public function createSite( $pathTarget, &$linkList )
	{
		return;
		$list		= array();
		foreach( $this->env->data->getFiles() as $fileId => $file )
		{
			if( !$file->errors )
				continue;
			$errors	= $file->errors;
			$label	= "<b>".$file->getPathname()."</b>";
			$list[]	= UI_HTML_Elements::ListItem( $label.$errors );
		}
		if( $list )
		{
			if( $this->env->verbose )
				remark( "Creating Site: ".$this->env->words['links']['encodingErrors'] );

			$uiData	= array(
				'list'	=> UI_HTML_Elements::unorderedList( $list ),
				'words'	=> $this->env->words['encoding'],
			);
			$content	= $this->loadTemplate( 'site/info/encoding', $uiData );
			file_put_contents( $pathTarget."encoding.html", $content );
/*			$linkList[]	= array(
				'url'	=> 'triggers.html',
				'label'	=> $this->env->words['links']['triggers'],
				'count'	=> count( $list )
			);*/
			return TRUE;
		}
		return FALSE;
	}
}
?>