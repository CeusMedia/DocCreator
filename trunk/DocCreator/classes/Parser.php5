<?php
/**
 *	Parses PHP Files containing a Class or Methods.
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
 *	@package		DocCreator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@since			04.08.08
 *	@version		$Id: Parser.php5 728 2009-10-20 06:39:43Z christian.wuerker $
 */
import( 'de.ceus-media.file.Reader' );
import( 'de.ceus-media.alg.StringUnicoder' );
import( 'model.File' );
import( 'model.Interface' );
import( 'model.Class' );
import( 'model.Variable' );
import( 'model.Member' );
import( 'model.Function' );
import( 'model.Method' );
import( 'model.Parameter' );
import( 'model.Author' );
import( 'model.License' );
import( 'model.Return' );
import( 'model.Throws' );
/**
 *	Parses PHP Files containing a Class or Methods.
 *	@category		cmTools
 *	@package		DocCreator
 *	@uses			File_Reader
 *	@uses			Model_File
 *	@uses			Model_Interface
 *	@uses			Model_Class
 *	@uses			Model_Variable
 *	@uses			Model_Member
 *	@uses			Model_Function
 *	@uses			Model_Method
 *	@uses			Model_Parameter
 *	@uses			Model_Author
 *	@uses			Model_License
 *	@uses			Model_Return
 *	@uses			Model_Throws
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@since			04.08.08
 *	@version		$Id: Parser.php5 728 2009-10-20 06:39:43Z christian.wuerker $
 *	@todo			Code Doc
 */
class Parser
{
	protected $regexClass		= '@^(abstract )?(final )?(interface |class )([\w]+)( extends ([\w]+))?( implements ([\w]+)(, ([\w]+))*)?(\s*{)?@i';
	protected $regexMethod		= '@^(abstract )?(final )?(protected |private |public )?(static )?function ([\w]+)\((.*)\)(\s*{\s*)?;?\s*$@';
	protected $regexParam		= '@^(([\w]+) )?((&\s*)?\$([\w]+))( ?= ?([\S]+))?$@';
	protected $regexDocParam	= '@^\*\s+\@param\s+(([\S]+)\s+)?(\$?([\S]+))\s*(.+)?$@';
	protected $regexDocVariable	= '@^/\*\*\s+\@var\s+(\w+)\s+\$(\w+)(\s(.+))?\*\/$@s';
	protected $regexVariable	= '@^(protected|private|public|var)\s+(static\s+)?\$(\w+)(\s+=\s+([^(]+))?.*$@';
	protected $varBlocks		= array();

	protected $openBlocks		= array();
	protected $lineNumber		= 0;


	/**
	 *	Appends all collected Documentation Information to already collected Code Information.
	 *	In general, found doc parser data are added to the php parser data.
	 *	Found doc data can contain strings, objects and lists of strings or objects.
	 *	Since parameters are defined in signature and doc block, they need to be merged.
	 *	Parameters are given with an associatove list indexed by parameter name.
	 *
	 *	@access		protected
	 *	@param		array		$codeData		Data collected by parsing Code
	 *	@param		string		$docData		Data collected by parsing Documentation
	 *	@return		void
	 *	@todo		fix merge problem
	 */
	protected function decorateCodeDataWithDocData( &$codeData, $docData )
	{
		foreach( $docData as $key => $value )
		{
			if( !$value )
				continue;

			if( is_object( $value ) )															//  value is an object
			{
				if( $codeData instanceof Model_Function )
				{
					switch( $key )
					{
						case 'return':		$codeData->setReturn( $value ); break;
					}
				}
			}
			else if( is_string( $value ) )																//  value is a simple string
			{
				switch( $key )
				{
					case 'category':	$codeData->setCategory( $value ); break;					//  extend category
					case 'package':		$codeData->setPackage( $value ); break;						//  extend package
					case 'version':		$codeData->setVersion( $value ); break;						//  extend version
					case 'since':		$codeData->setSince( $value ); break;						//  extend since
					case 'description':	$codeData->setDescription( $value ); break;					//  extend description
					case 'todo':		$codeData->setTodo( $itemValue ); break;					//  extend todos
				}
				if( $codeData instanceof Model_Interface )
				{
					switch( $key )
					{
						case 'access':		$codeData->setAccess( $value ); break;					//  extend access
						case 'extends':		$codeData->setExtendedClassName( $value ); break;		//  extend extends
					}
				}
			}
			else if( is_array( $value ) )															//  value is a list of objects or strings
			{
				foreach( $value as $itemKey => $itemValue )											//  iterate list
				{
					if( is_string( $itemKey ) )														//  special case: value is associative array -> a parameter to merge
					{
						switch( $key )
						{
							case 'param':
								foreach( $codeData->getParameters() as $parameter )
									if( $parameter->getName() == $itemKey )
										$parameter->merge( $itemValue );
								break;
						}
					}
					else																			//  value is normal list of objects or strings
					{
						switch( $key )
						{
							case 'license':		$codeData->setLicense( $itemValue ); break;
							case 'copyright':	$codeData->setCopyright( $itemValue ); break;
							case 'author':		$codeData->setAuthor( $itemValue ); break;
							case 'link':		$codeData->setLink( $itemValue ); break;
							case 'see':			$codeData->setSee( $itemValue ); break;
							case 'deprecated':	$codeData->setDeprecation( $itemValue ); break;
							case 'todo':		$codeData->setTodo( $itemValue ); break;
						}
						if( $codeData instanceof Model_Interface )
						{
							switch( $key )
							{
								case 'implements':	$codeData->setImplementedInterfaceName( $itemValue ); break;
								case 'uses':		$codeData->setUsedClassName( $itemValue ); break;
							}
						}
						else if( $codeData instanceof Model_Function )
						{
							switch( $key )
							{
								case 'throws':		$codeData->setThrows( $itemValue ); break;
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 *	Parses a Class Signature and returns collected Information.
	 *	@access		protected
	 *	@param		Model_File		$parent			File Object of current Class
	 *	@param		array			$matches		Matches of RegEx
	 *	@return		Model_Class
	 */
	protected function parseClass( Model_File $parent, $matches )
	{
		$class	= new Model_Class( $matches[4] );
		$class->setParent( $parent );
		$class->setLine( $this->lineNumber );
		$class->setAbstract( (bool) $matches[1] );
		$class->setFinal( (bool) $matches[2] );
		$class->type		= $matches[3];
		$class->setExtendedClassName( isset( $matches[5] ) ? $matches[6] : NULL );
		if( isset( $matches[7] ) )
			foreach( array_slice( $matches, 8 ) as $match )
				if( trim( $match ) && !preg_match( "@^,|{@", trim( $match ) ) )
					$class->setImplementedInterfaceName( trim( $match ) );
		if( $this->openBlocks )
		{
			$this->decorateCodeDataWithDocData( $class, array_pop( $this->openBlocks ) );
			$this->openBlocks	= array();
		}
		return $class;
	}

	/**
	 *	Parses a Doc Block and returns Array of collected Information.
	 *	@access		protected
	 *	@param		array		$lines			Lines of Doc Block
	 *	@return		array
	 */
	protected function parseDocBlock( $lines )
	{
		$data		= array();
		$descLines	= array();
		foreach( $lines as $line )
		{
			if( preg_match( $this->regexDocParam, $line, $matches ) )
			{
				$data['param'][$matches[4]]	= $this->parseDocParameter( $matches );
			}
			else if( preg_match( "@\*\s+\@return\s+(\w+)\s*(.+)?$@i", $line, $matches ) )
			{
				$data['return']	= $this->parseDocReturn( $matches );
			}
			else if( preg_match( "@\*\s+\@throws\s+(\w+)\s*(.+)?$@i", $line, $matches ) )
			{
				$data['throws'][]	= $this->parseDocThrows( $matches );
			}
			else if( preg_match( "@\*\s+\@author\s+(.+)\s*(<(.+)>)?$@iU", $line, $matches ) )
			{
				$author	= new Model_Author( trim( $matches[1] ) );
				if( isset( $matches[3] ) )
					$author->setEmail( trim( $matches[3] ) );
				$data['author'][]	= $author;
			}
			else if( preg_match( "@\*\s+\@license\s+(\S+)( .+)?$@i", $line, $matches ) )
			{
				$data['license'][]	= $this->parseDocLicense( $matches );
			}
			else if( preg_match( "/^\*\s+@(\w+)\s*(.*)$/", $line, $matches ) )
			{
				switch( $matches[1] )
				{
					case 'implements':
					case 'deprecated':
					case 'todo':
					case 'copyright':
					case 'license':
					case 'author':
					case 'see':
					case 'uses':
					case 'link':
						$data[$matches[1]][]	= $matches[2];			
						break;
					default:
					#	$data[$matches[1]]	= $matches[2];			
						break;
				}
			}
			else if( !$data && preg_match( "/^\*\s*([^@].+)?$/", $line, $matches ) )
				$descLines[]	= isset( $matches[1] ) ? trim( $matches[1] ) : "";
		}
		$data['description']	= trim( implode( "\n", $descLines ) );

		if( !isset( $data['throws'] ) )
			$data['throws']	= array();
		return $data;
	}

	/**
	 *	Parses a File/Class License Doc Tag and returns collected Information.
	 *	@access		protected
	 *	@param		array		$matches		Matches of RegEx
	 *	@return		Model_License
	 */
	protected function parseDocLicense( $matches )
	{
		$name	= NULL;
		$url	= NULL;
		if( isset( $matches[2] ) )
		{
			$url	= trim( $matches[1] );
			$name	= trim( $matches[2] );
			if( preg_match( "@^http://@", $matches[2] ) )
			{
				$url	= trim( $matches[2] );
				$name	= trim( $matches[1] );
			}
		}
		else
		{
			$name	= trim( $matches[1] );
			if( preg_match( "@^http://@", $matches[1] ) )
				$url	= trim( $matches[1] );
		}
		$license	= new Model_License( $name, $url );
		return $license;
	}

	/**
	 *	Parses a Class Member Doc Tag and returns collected Information.
	 *	@access		protected
	 *	@param		array		$matches		Matches of RegEx
	 *	@return		Model_Member
	 */
	protected function parseDocMember( $matches )
	{
		$member	= new Model_Member( $matches[2], $matches[1], trim( $matches[4] ) );
		return $member;
	}

	/**
	 *	Parses a Function/Method Parameter Doc Tag and returns collected Information.
	 *	@access		protected
	 *	@param		array		$matches		Matches of RegEx
	 *	@return		Model_Parameter
	 */
	protected function parseDocParameter( $matches )
	{
		$parameter	= new Model_Parameter( $matches[4], $matches[2] );
		if( isset( $matches[5] ) )
			$parameter->setDescription( $matches[5] );
		return $parameter;
	}

	/**
	 *	Parses a Function/Method Return Doc Tag and returns collected Information.
	 *	@access		protected
	 *	@param		array		$matches		Matches of RegEx
	 *	@return		Model_Return
	 */
	protected function parseDocReturn( $matches )
	{
		$return	= new Model_Return( trim( $matches[1] ) );
		if( isset( $matches[2] ) )
			$return->setDescription( trim( $matches[2] ) );
		return $return;
	}

	/**
	 *	Parses a Function/Method Throws Doc Tag and returns collected Information.
	 *	@access		protected
	 *	@param		array		$matches		Matches of RegEx
	 *	@return		Model_Throws
	 */
	protected function parseDocThrows( $matches )
	{
		$throws	= new Model_Throws( trim( $matches[1] ) );
		if( isset( $matches[2] ) )
			$throws->setReason( trim( $matches[2] ) );
		return $throws;
	}

	/**
	 *	Parses a Class Varible Doc Tag and returns collected Information.
	 *	@access		protected
	 *	@param		array		$matches		Matches of RegEx
	 *	@return		Model_Variable
	 */
	protected function parseDocVariable( $matches )
	{
		$variable	= new Model_Variable( $matches[2], $matches[1], trim( $matches[4] ) );
		return $variable;
	}

	/**
	 *	Parses a PHP File and returns nested Array of collected Information.
	 *	@access		public
	 *	@param		string		$fileName		File Name of PHP File to parse
	 *	@param		string		$innerPath		Base Path to File to be removed in Information
	 *	@return		array
	 */
	public function parseFile( $fileName, $innerPath )
	{
		$content		= File_Reader::load( $fileName );
		if( !Alg_StringUnicoder::isUnicode( $content ) )
			$content		= Alg_StringUnicoder::convertToUnicode( $content );

		$lines			= explode( "\n", $content );
		$fileBlock		= NULL;
		$openClass		= FALSE;
		$function		= NULL;
		$functionBody	= array();
		$file			= new Model_File;
		$file->setBasename( basename( $fileName ) );
		$file->setPathname( substr( str_replace( "\\", "/", $fileName ), strlen( $innerPath ) ) );
		$file->setUri( str_replace( "\\", "/", $fileName ) );
	
		$level	= 0;
		$class	= NULL;
		do
		{
			$line	= trim( array_shift( $lines ) );
			$this->lineNumber ++;
			if( preg_match( "@^(<\?(php)?)|((php)?\?>)$@", $line ) )
				continue;
			
			if( preg_match( '@^\s*{ ?}?$@', $line ) )
				$level++;
			if( preg_match( '@}$@', $line ) )
				$level--;

			if( !$function && $line == "/**" )
			{
				$list	= array();
				while( !preg_match( "@\*?\*/\s*$@", $line ) )
				{
					$list[]	= $line;
					$line	= trim( array_shift( $lines ) );
					$this->lineNumber ++;
				}
				array_unshift( $lines, $line );
				$this->lineNumber --;
				if( $list )
				{
					$this->openBlocks[]	= $this->parseDocBlock( $list );
					if( !$fileBlock && !$class )
					{
						$fileBlock	= array_shift( $this->openBlocks );
						array_unshift( $this->openBlocks, $fileBlock );
						$this->decorateCodeDataWithDocData( $file, $fileBlock );
					}
				}
				continue;
			}
			if( !$openClass )
			{
				if( preg_match( $this->regexClass, $line, $matches ) )
				{
					if( trim( array_pop( array_slice( $matches, -1 ) ) ) == "{" )
					{
						array_pop( $matches );
						$level++;
					}
					while( !trim( array_pop( array_slice( $matches, -1 ) ) ) )
						array_pop( $matches );
					$class	= $this->parseClass( $file, $matches );
					$openClass	= TRUE;
				}
				else if( preg_match( $this->regexMethod, $line, $matches ) )
				{
					$function	= $this->parseFunction( $file, $matches );
					if( isset( $matches[8] ) )
						$level++;
					$file->setFunction( $function->getName(), $function );
				}
			}
			else
			{
				if( preg_match( $this->regexClass, $line, $matches ) )
				{
					$file->setClass( $class->getId(), $class );
					array_unshift( $lines, $line );
					$this->lineNumber --;
					$openClass	= FALSE;
					$level		= 0;
					continue;
				}
				if( $level == 0 && $openClass )
					$openClass	= FALSE;
				if( preg_match( $this->regexMethod, $line, $matches ) )
				{
					$method		= $this->parseMethod( $class, $matches );
					$function	= $matches[5];
					$class->setMethod( $method->getName(), $method );
					if( isset( $matches[8] ) )
						$level++;
				}
				else if( preg_match( $this->regexDocVariable, $line, $matches ) )
				{
					if( $openClass && $class )
						$this->varBlocks[$class->getName()."::".$matches[2]]	= $this->parseDocMember( $matches );
					else
						$this->varBlocks[$matches[2]]	= $this->parseDocVariable( $matches );
				}
				else if( preg_match( $this->regexVariable, $line, $matches ) )
				{
					$name		= $matches[3];
					if( $openClass && $class )
					{
						$key		= $class->getName()."::".$name;
						$varBlock	= isset( $this->varBlocks[$key] ) ? $this->varBlocks[$key] : NULL;
						$variable	= $this->parseMember( $class, $matches, $varBlock ); 
						$class->setMember( $name, $variable );
					
					}
					else
					{
						remark( "Parser Error: found var after class -> not handled yet" );
/*						$key		= $name;
						$varBlock	= isset( $this->varBlocks[$key] ) ? $this->varBlocks[$key] : NULL;
						$variable	= $this->parseMember( $matches, $varBlock );*/
					}
				}
				else if( $level > 1 && $function )
				{
					$functionBody[$function][]	= $line;
				}
			}
		}
		while( $lines );


		$file->setSourceCode( $content );
		if( $class )
		{
			foreach( $class->getMethods() as $methodName => $method )
				if( isset( $functionBody[$methodName] ) )
					$method->sourceCode	= $functionBody[$methodName];
			$file->setClass( $class->getName(), $class );
		}
		return $file;
	}

	/**
	 *	Parses a Function Signature and returns collected Information.
	 *	@access		protected
	 *	@param		Model_File		$parent			Parent File Data Object
	 *	@param		array			$matches		Matches of RegEx
	 *	@return		Model_Function
	 */
	protected function parseFunction( Model_File $parent, $matches )
	{
		$function	= new Model_Function( $matches[5] );
		$function->setParent( $parent );
		$function->setLine( $this->lineNumber );

		if( trim( $matches[6] ) )
		{
			$paramList	= array();
			foreach( explode( ",", $matches[6] ) as $param )
			{
				$param	 = trim( $param );
				if( !preg_match( $this->regexParam, $param, $matches ) )
					continue;
				$function->setParameter( $this->parseParameter( $function, $matches ) );
			}
		}
		if( $this->openBlocks )
		{
			$methodBlock	= array_pop( $this->openBlocks );
			$this->decorateCodeDataWithDocData( $function, $methodBlock );
			$this->openBlocks	= array();
		}
		return $function;
	}

	/**
	 *	Parses a Class Member Signature and returns collected Information.
	 *	@access		protected
	 *	@param		Model_Class		$parent			Parent Class Data Object
	 *	@param		array			$matches		Matches of RegEx
	 *	@param		array			$docBlock		Variable data object from Doc Parser
	 *	@return		Model_Member
	 */
	protected function parseMember( $parent, $matches, $docBlock = NULL )
	{
		$variable			= new Model_Member( $matches[3], NULL, NULL );
		$variable->setParent( $parent );
		$variable->setLine( $this->lineNumber );
		if( isset( $matches[4] ) )
			$variable->setDefault( preg_replace( "@;$@", "", $matches[5] ) );
		$variable->setAccess( $matches[1] == "var" ? "public" : $matches[1] );
		$variable->setStatic( (bool) trim( $matches[2] ) );

		if( $docBlock )
			if( $docBlock instanceof Model_Variable )
				if( $docBlock->getName() == $variable->getName() )
					$variable->merge( $docBlock );
		return $variable;
	}

	/**
	 *	Parses a Method Signature and returns collected Information.
	 *	@access		protected
	 *	@param		Model_Class		$parent			Parent Class Data Object
	 *	@param		array			$matches		Matches of RegEx
	 *	@return		Model_Method
	 */
	protected function parseMethod( Model_Class $parent, $matches )
	{
		$method	= new Model_Method( $matches[5] );
		$method->setParent( $parent );
		$method->setLine( $this->lineNumber );
		$method->setAccess( trim( $matches[3] ) );
		$method->setAbstract( (bool) $matches[1] );
		$method->setFinal( (bool) $matches[2] );
		$method->setStatic( (bool) $matches[4] );

		$return		= new Model_Return( "unknown" );
		$return->setParent( $method );
		$method->setReturn( $return );

		if( trim( $matches[6] ) )
		{
			$paramList	= array();
			foreach( explode( ",", $matches[6] ) as $param )
			{
				$param	 = trim( $param );
				if( !preg_match( $this->regexParam, $param, $matches ) )
					continue;
				$method->setParameter( $this->parseParameter( $method, $matches ) );
			}
		}
		if( $this->openBlocks )
		{
			$methodBlock	= array_pop( $this->openBlocks );
			$this->decorateCodeDataWithDocData( $method, $methodBlock );
			$this->openBlocks	= array();
		}
#		if( !$method->getAccess() )
#			$method->setAccess( 'public' );
		return $method;
	}

	/**
	 *	Parses a Function/Method Signature Parameters and returns collected Information.
	 *	@access		protected
	 *	@param		Model_Function	$parent			Parent Function or Method Data Object
	 *	@param		array			$matches		Matches of RegEx
	 *	@return		Model_Parameter
	 */
	protected function parseParameter( Model_Function $parent, $matches )
	{
		$parameter	= new Model_Parameter( $matches[5] );
		$parameter->setParent( $parent );
		$parameter->setLine( $this->lineNumber );
		$parameter->setCast( $matches[2] );
		$parameter->setReference( (bool) $matches[4] );

		if( isset( $matches[6] ) )
			$parameter->setDefault( $matches[7] );
		return $parameter;
	}
}
?>