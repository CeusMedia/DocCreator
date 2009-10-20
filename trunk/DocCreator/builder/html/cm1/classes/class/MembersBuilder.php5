<?php
/**
 *	Builds Class Members Information File.
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
 *	@package		DocCreator_Builder_HTML_CM1_Class
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: MembersBuilder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.class.InfoBuilder' );
/**
 *	Builds Class Members Information File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Class
 *	@extends		Builder_HTML_CM1_Class_InfoBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: MembersBuilder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Builder_HTML_CM1_Class_MembersBuilder extends Builder_HTML_CM1_Class_InfoBuilder
{
	/**
	 *	Builds View of Class Members for Class Information File.
	 *	@access		public
	 *	@param		Model_Class	$class			Class Object
	 *	@return		string
	 */
	public function buildView( Model_Class $class )
	{
		$this->type	= "class";
		
		$list		= array();
		$members	= $class->getMembers();
		ksort( $members );
		foreach( $members as $memberName => $member )
			$list[$memberName]	= $this->buildMemberEntry( $class, $memberName, $member );


		$content	= "";
		if( $list )
		{
			$words		= $this->env->words['classMembers'];
			$heading	= sprintf( $words['heading'], $class->getName() );
			$data	= array(
				'words'		=> $words,
				'heading'	=> $heading,
				'list'		=> implode( "\n", $list ),
			);
			$content	= $this->loadTemplate( 'class.members', $data );
		}
		$content	.= $this->buildInheritedMemberList( $class, array_keys( $list ) );
		return $content;
	}

	/**
	 *	Builds List of inherited Members of all extended Classes.
	 *	@access		public
	 *	@param		Model_Class	$class			Class Object
	 *	@param		array		$got			List of Member Names already handled
	 *	@return		string		List HTML 
	 */
	private function buildInheritedMemberList( Model_Class $class, $got = array() )
	{	
		$extended	= array();
		$members	= $class->getMembers();
		$classes	= $this->getSuperClasses( $class );
		foreach( $classes as $superClass )
		{
			$list		= array();
			if( !is_object( $superClass ) )
				continue;
			foreach( $superClass->getMembers() as $memberName => $member )
			{
				if( in_array( $member, $members ) )
					continue;
				if( in_array( $member, $got ) )
					continue;
				if( $member->getAccess() == 'private' )
					continue;
				$got[]		= $memberName;
				$uri		= 'class.'.$superClass->getId().".html#class_member_".$memberName;
				$link		= UI_HTML_Elements::Link( $uri, $memberName );
				$list[$memberName]	= UI_HTML_Elements::ListItem( $link, 1 );
			}
			if( $list )
			{
				ksort( $list );
				$list		= UI_HTML_Elements::unorderedList( $list );
				$item		= $this->getTypeMarkUp( $class ).$list;
				$attributes	= array( 'class' => 'membersOfExtendedClass' );
				$extended[]	= UI_HTML_Elements::ListItem( $item, 0, $attributes );
			}
		}
		if( !$extended )
			return "";
		$attributes	= array( 'class' => 'extendedClass' );
		$extended	= UI_HTML_Elements::unorderedList( $extended, 0, $attributes );
		$data	= array(
			'words'	=> $this->words['classMembersInherited'],
			'list'	=> $extended,
		);
		return $this->loadTemplate( 'class.members.inherited', $data );
	}

	/**
	 *	Builds View of a Member with all Information.
	 *	@access		private
	 *	@param		Model_Class		$class			Class Object
	 *	@param		string			$memberName		Name of Member
	 *	@param		Model_Member	$member			Member data object
	 *	@return		string
	 */
	private function buildMemberEntry( Model_Class $class, $memberName, $member )
	{
		$default	= $member->getDefault() ? " = ".$member->getDefault() : "";
		$type		= $member->getType() ? $this->getTypeMarkUp( $member->getType() ) : "";

		$attributes	= array();
		$attributes['access']	= $this->buildParamStringList( $member->getAccess(), 'access' );
		$attributes['type']		= $this->buildParamClassList( $member, $member->getType(), 'type' );
		$attributes['default']	= $this->buildParamStringList( $member->getDefault(), 'default' );

		$attributes	= $this->loadTemplate( 'class.member.attributes', $attributes );
	
		$data	= array(
			'memberName'	=> $memberName,
			'memberTitle'	=> '$'.$memberName,
			'access'		=> $member->getAccess(),
			'type'			=> $type,
			'default'		=> $default,
			'attributes'	=> $attributes,
			'description'	=> nl2br( trim( $member->getDescription() ) ),
		);
		return $this->loadTemplate( 'class.member', $data );
	}
}
?>