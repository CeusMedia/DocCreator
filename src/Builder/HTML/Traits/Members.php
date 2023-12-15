<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Class Members Information File.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Classes
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Traits;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Traits\Info as TraitInfo;
use CeusMedia\PhpParser\Structure\Trait_ as PhpTrait;
use CeusMedia\PhpParser\Structure\Member_ as PhpMember;

/**
 *	Builds Class Members Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Classes
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Members extends TraitInfo
{
	/**
	 *	Builds List of inherited Members of all extended Classes.
	 *	@access		public
	 *	@param		PhpTrait		$trait			Class Object
	 *	@param		array			$got			List of Member Names already handled
	 *	@return		string			List HTML
	 */
	private function buildInheritedMemberList( PhpTrait $trait, array $got = [] ): string
	{
		$extended		= [];
		$memberNames	= array_keys( $trait->getMembers() );										//  we only need a list of method names for comparison

		$traits		= $this->getSuperClasses( $trait );
		foreach( $traits as $nr => $superClass ){
			$list		= [];
			if( !is_object( $superClass ) )
				continue;
			foreach( $superClass->getMembers() as $memberName => $member ){
				if( in_array( $memberName, $memberNames ) )
					continue;
				if( in_array( $memberName, $got ) )
					continue;
				if( $member->getAccess() == 'private' )
					continue;
				$got[]		= $memberName;
				$uri		= 'trait.'.$superClass->getId().".html#class_member_".$memberName;
				$link		= HtmlElements::Link( $uri, $memberName, 'member' );
				$linkTyped	= $this->getTypeMarkUp( $link );
				$list[$memberName]	= HtmlElements::ListItem( $linkTyped, 1, ['class' => 'member'] );
			}
			if( 0 !== count( $list ) ){
				ksort( $list );
				$list		= HtmlElements::unorderedList( $list );
				$item		= $this->getTypeMarkUp( $superClass ).$list;
				$attributes	= array( 'class' => 'membersOfExtendedClass' );
				if( $nr % 3 == 0 )
					$attributes['style']	= "clear: left";										//  line break after each 3 classes
				$extended[]	= HtmlElements::ListItem( $item, 0, $attributes );
			}
		}
		if( !$extended )
			return "";
		$attributes	= array( 'class' => 'extendedClass' );
		$extended	= HtmlElements::unorderedList( $extended, 0, $attributes );
		$data	= [
			'words'	=> $this->words['classMembersInherited'],
			'list'	=> $extended,
		];
		return $this->loadTemplate( 'class.members.inherited', $data );
	}

	/**
	 *	Builds View of a Member with all Information.
	 *	@access		private
	 *	@param		string			$memberName		Name of Member
	 *	@param		PhpMember	$member			Member data object
	 *	@return		string
	 */
	private function buildMemberEntry( string $memberName, PhpMember $member ): string
	{
		$default	= $member->getDefault() ? " = ".$member->getDefault() : "";
		$type		= $member->getType() ? $this->getTypeMarkUp( $member->getType() ) : "";

		$attributes	= [];
		$accessType	= $member->getAccess() ?: 'unknown';
		$access		= $this->buildAccessLabel( $accessType );
		$attributes['access']	= $this->buildParamStringList( $access, 'access' );
		$attributes['type']		= $this->buildParamClassList( $member, $member->getType(), 'type' );
		$attributes['default']	= $this->buildParamStringList( str_replace( ['<%', '%>'], ['[%', '%]'], $member->getDefault() ), 'default' );

		$attributes	= $this->loadTemplate( 'class.member.attributes', $attributes );

		$accessType	= $member->getAccess() ?: 'public';
		$data	= array(
			'memberName'	=> $memberName,
			'memberTitle'	=> '$'.$memberName,
			'access'		=> $accessType,
			'type'			=> $type,
			'default'		=> str_replace( array( '<%', '%>' ), array( '[%', '%]' ), $default ),
			'attributes'	=> $attributes,
			'description'	=> $this->getFormatedDescription( $member->getDescription() ),
		);
		return $this->loadTemplate( 'class.member', $data );
	}

	/**
	 *	Builds View of Class Members for Class Information File.
	 *	@access		public
	 *	@param		PhpTrait	$trait			Class Object
	 *	@return		string
	 */
	public function buildView( object $trait ): string
	{
		/** @var PhpTrait $trait */

		$this->type	= "trait";

		$list		= [];
		$members	= $trait->getMembers();
		ksort( $members );
		foreach( $members as $memberName => $member )
			$list[$memberName]	= $this->buildMemberEntry( $memberName, $member );

		$content	= "";
		if( $list ){
			$words		= $this->env->words['classMembers'];
			$heading	= sprintf( $words['heading'], $trait->getName() );
			$data	= array(
				'words'		=> $words,
				'heading'	=> $heading,
				'list'		=> implode( "\n", $list ),
			);
			$content	= $this->loadTemplate( 'class.members', $data );
		}
		$content	.= $this->buildInheritedMemberList( $trait, array_keys( $list ) );
		return $content;
	}
}
