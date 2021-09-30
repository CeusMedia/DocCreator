<?php
/**
 *	Builder for Index View.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\File;

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;
use CeusMedia\PhpParser\Structure\Member_ as PhpMember;
use CeusMedia\PhpParser\Structure\Method_ as PhpMethod;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;

use UI_HTML_Elements as HtmlElements;
use UI_HTML_Tag as HtmlTag;

define( 'RELATION_EXTENDS', 1 );
define( 'RELATION_IMPLEMENTS', 2 );

/**
 *	Builder for Index View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Index extends HtmlBuilderAbstraction
{
	private $list	= [];

	/**
	 *	Builds Index View.
	 *	@access		public
	 *	@param		PhpFile	$file			File Object to build Index for
	 *	@return		string
	 */
	public function buildIndex( PhpFile $file ): string
	{
		$all		= array_merge( $file->getClasses(), $file->getInterfaces() );
		$class		= array_shift( $all );
		$words		= $this->env->words['index'];
		$this->list	= array();

		//  --  FILE INFO  --  //
		$this->addMainLink( 'file-info', $words['file'] );

		if( $class ){
			//  --  CLASS INFO  --  //
			$this->addMainLink( 'class-info', $words['class'] );

			//  --  CLASS MEMBERS & INHERITED CLASS MEMBERS  --  //
			if( $class instanceof PhpClass ){
				$inheritedMemberList	= $this->buildInheritedMemberList( $class, RELATION_EXTENDS );
				$memberList	= $this->buildMemberList( $class );
				if( $memberList ){
					if( $inheritedMemberList )
						foreach( array_keys( $memberList ) as $memberName )
							unset( $inheritedMemberList[$memberName] );
					$this->addMainLink( "class-members", $words['classMembers'], $memberList );
				}
				if( $inheritedMemberList )
					$this->addMainLink( 'class-members-inherited', $words['classMembersInherited'], $inheritedMemberList );
			}

			//  --  CLASS METHODS & INHERITED CLASS METHODS  --  //
			$inheritedMethodList	= $this->buildInheritedMethodList( $class, RELATION_EXTENDS );
			$methodList	= $this->buildMethodList( $class );
			if( $methodList ){
				if( $inheritedMethodList )
					foreach( array_keys( $methodList ) as $methodName )
						unset( $inheritedMethodList[$methodName] );
				$this->addMainLink( "class-methods", $words['classMethods'], $methodList );
			}
			if( $inheritedMethodList )
				$this->addMainLink( 'class-methods-inherited', $words['classMethodsInherited'], $inheritedMethodList );
		}

		//  --  FILE FUNCTIONS  --  //
		if( $file->hasFunctions() ){
			$functionList	= array();
			foreach( $file->getFunctions() as $name => $function ){
				$a		= explode( "\n", $function->getDescription() );
				$desc	= array_shift( $a );
				$label	= HtmlElements::Acronym( $name, $desc );
				$link	= HtmlElements::Link( "#file_function_".$name, $label );
				$class	= 'index-function';
				$item	= HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
				$functionList[]	= $item;
			}
			$this->addMainLink( 'file-functions', $words['functions'], $functionList );
		}

		//  --  FILE SOURCE  --  //
		$options	= new \ADT_List_Dictionary( $this->env->getBuilderOptions() );
		if( $options->get( 'showSourceCode' ) )
			$this->addMainLink( 'file-source', $words['sourceCode'] );


//		$indexList	= HtmlElements::unorderedList( $this->list );
		$indexList	= HtmlTag::create( 'ul', $this->list, array( 'class' => 'nav' ) );
		$indexList	= HtmlTag::create( 'div', $indexList, array( 'class' => 'navbar-inner' ) );
		$indexList	= HtmlTag::create( 'div', $indexList, array( 'class' => 'navbar navbar-fixed-top' ) );
		$data		= array(
			'words'	=> $this->env->words['index'],
			'list'	=> $indexList,
		);
		return $this->loadTemplate( 'site/index', $data );
	}

	/**
	 *	Adds a Main Link to the Index List.
	 *	@access		private
	 *	@param		string		$class			Item Class or Linked Anchor ID
	 *	@param		string		$label			Label of Main Link
	 *	@param		array|string		$content		Content within Main Link
	 *	@return		void
	 */
	private function addMainLink( string $class, string $label, $content = '' )
	{
		$class	= str_replace( "_", "-", $class );
		$url	= "#".str_replace( "-", "_", $class );
		if( $content && is_array( $content ) ){
			$caret		= HtmlTag::create( 'b', '', array( 'class' => 'caret' ) );
//			$content	= HtmlElements::unorderedList( $content );
			$content	= HtmlTag::create( 'ul', $content, array( 'class' => 'dropdown-menu' ) );
			$link		= HtmlTag::create( 'a', $label.$caret, array( 'href' => /*$url*/'#index-'.$class, 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown' ) );
			$item		= HtmlTag::create( 'li', $link.$content, array( 'class' => 'dropdown index-'.$class ) );
		}
		else{
			$link	= HtmlElements::Link( $url, $label ).$content;
			$item	= HtmlElements::ListItem( $link, 0, array( 'class' => 'index-'.$class ) );
		}
		$this->list[]	= $item;
	}

	/**
	 *	Builds List of inherited Members.
	 *	@access		private
	 *	@param		PhpClass		$class			Class Object to get inherited Member List for
	 *	@return		array
	 */
	private function buildInheritedMemberList( PhpClass $class ): array
	{
		$list		= array();
		$superClass	= $class->getExtendedClass();
		if( is_object( $superClass ) ){
			$subList	= $this->buildInheritedMemberList( $superClass );
			$memberList	= $this->buildMemberList( $superClass, RELATION_EXTENDS );
			$list		= array_merge( $subList, $memberList );
			ksort( $list );
		}
		return $list;
	}

	/**
	 *	Builds List of inherited Methods.
	 *	@access		private
	 *	@param		PhpInterface		$class			Class Object to get inherited Method List for
	 *	@return		array
	 */
	private function buildInheritedMethodList( PhpInterface $class ): array
	{
		$list		= array();
		if( $class instanceof PhpClass )
			$superClass	= $class->getExtendedClass();
		else if( $class instanceof PhpInterface )
			$superClass	= $class->getExtendedInterface();
		if( isset( $superClass ) && is_object( $superClass ) ){
			$subList	= $this->buildInheritedMethodList( $superClass );
			$methodList	= $this->buildMethodList( $superClass, RELATION_EXTENDS );
			$list		= array_merge( $subList, $methodList );
			ksort( $list );
		}
		return $list;
	}

	/**
	 *	Builds List Item of Member.
	 *	@access		private
	 *	@param		PhpClass		$class			Class Object
	 *	@param		string			$memberName		Name of Member
	 *	@param		PhpMember	$memberData		Information of Member
	 *	@return		string
	 */
	private function buildMemberEntry( PhpClass $class, string $memberName, PhpMember $memberData ): string
	{
		$desc	= explode( "\n", $memberData->getDescription() );
		$desc	= array_shift( $desc );
		$label	= $desc ? HtmlElements::Acronym( $memberName, $desc ) : $memberName;
		$uri	= 'class.'.$class->getId().".html#class_member_".$memberName;
		$link	= HtmlElements::Link( $uri, $label );
		$class	= 'index-member-'.$memberData->getAccess();
		return HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
	}

	/**
	 *	Builds List of Members.
	 *	@access		private
	 *	@param		PhpClass	$class			Class Object
	 *	@param		int			$relation		Flag: hide private Members
	 *	@return		array
	 */
	private function buildMemberList( PhpClass $class, $relation = 0 ): array
	{
		$list		= array();
		$members	= $class->getMembers();
		ksort( $members );
		foreach( $members as $memberName => $memberData ){
			if( $relation )
				if( $memberData->getAccess() == "private")
					continue;
			$list[$memberName]	= $this->buildMemberEntry( $class, $memberName, $memberData );
		}
		return $list;
	}

	/**
	 *	Builds List Item of Method.
	 *	@access		private
	 *	@param		PhpInterface	$class			Class Object
	 *	@param		string			$methodName		Name of Method
	 *	@param		PhpMethod	$methodData		Information of Method
	 *	@return		string
	 */
	private function buildMethodEntry( PhpInterface $class, string $methodName, PhpMethod $methodData ): string
	{
		$desc	= explode( "\n", $methodData->getDescription() );
		$desc	= array_shift( $desc );
		$label	= $desc ? HtmlElements::Acronym( $methodName, $desc ) : $methodName;
		$uri	= 'interface.'.$class->getId().".html#interface_method_".$methodName;
		if( $class instanceof PhpClass )
			$uri	= 'class.'.$class->getId().".html#class_method_".$methodName;
		$link	= HtmlElements::Link( $uri, $label );
		$class	= 'index-method-'.$methodData->getAccess();
		return HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
	}

	/**
	 *	Builds List of Methods.
	 *	@access		private
	 *	@param		PhpInterface	$class			Class Object
	 *	@param		int				$relation		Flag: hide final, abstract and private Methods
	 *	@return		array
	 */
	private function buildMethodList( PhpInterface $class, $relation = 0 ): array
	{
		$list		= array();
		$methods	= $class->getMethods();
		ksort( $methods );
		foreach( $methods as $methodName => $methodData ){
			if( $relation )
				if( $methodData->isFinal() || $methodData->isAbstract() || $methodData->getAccess() == "private" )
					continue;
			$list[$methodName]	= $this->buildMethodEntry( $class, $methodName, $methodData );
		}
		return $list;
	}
}

