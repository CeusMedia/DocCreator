<?php
/**
 *	Collects Relations between Classes.
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
 *	@package		DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Relations.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'reader.plugin.Abstract' );
/**
 *	Collects Relations between Classes.
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@extends		Reader_Plugin_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Relations.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Reader_Plugin_Relations extends Reader_Plugin_Abstract
{
	/**
	 *	Collects Relations between Classes.
	 *	@access		protected
	 *	@param		Model_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( Model_Container $data )
	{
		if( $this->verbose )
			remark( "Finding Class Relations..." );
		$extendedBy		= array();
		$implementedBy	= array();
		$usedBy			= array();
		$composedBy		= array();
		$receivedBy		= array();
		$returnedBy		= array();

		foreach( $data->getFiles() as $fileName => $file )
		{
			foreach( $file->getClasses() as $class )
			{
				if( $class->getUsedClasses() )														//  current class uses other classes
				{
					foreach( $class->getUsedClasses() as $nr => $className )						//  iterate used classes
					{
						$usedClass	= $data->getClassFromClassName( $className, $class );			//  try to resolve class to object
						if( !$usedClass )															//  used class is not known
							continue;																//  skip to next one
						$usedBy[$usedClass->getId()][]	= $class;									//  note the resolved class uses current class
						$class->setUsedClass( $usedClass );											//  store resolved class object instead of class name string
					}
				}
				if( $class->getImplementedInterfaces() )											//  current class implements interfaces
				{
					foreach( $class->getImplementedInterfaces() as $nr => $className )				//  iterate interfaces
					{
						$implementedClass	= $data->getClassFromClassName( $className, $class );	//  try to resolve interface to object
						if( !$implementedClass )													//  implemented interface is not known
							continue;																//  skip to next one
				
						$implementedBy[$implementedClass->getId()][]	= $implementedClass;		//  note that resolved interface is implemented by currend class
						$class->setImplementedInterface( $implementedClass );						//  store resolved interface object instead of interface name string
					}
				}
				if( $class->getExtendedClass() )																//  current class is extending another class
				{
					$superClass		= $class->getExtendedClass();
					$extendedClass	= $data->getClassFromClassName( $superClass, $class );			//  try to resolve extended class to object
					if( $extendedClass )															//  extended class is known
					{
						$extendedBy[$extendedClass->getId()]	= $extendedClass;					//  note that extended class is extended by current class
						$class->setExtendedClass( $extendedClass );									//  store resolved class object instead of class name string
					}
				}
				foreach( $class->getMembers() as $member )
				{
					if( !$member->getType() )
						continue;
					$foundClass	= $data->getClassFromClassName( $member->getType(), $class );		//  try to resolve extended class to object
					if( !$foundClass )																//  extended class is known
						continue;
					$member->setType( $foundClass );
					$composedBy[$foundClass->getId()][]	= $foundClass;
				}

				foreach( $class->getMethods() as $method )
				{
					foreach( $method->getParameters() as $parameter )
					{
						if( !$parameter->getType() )
							continue;
						$type	= $parameter->getType();											//  get type of parameter
						$foundClass	= $data->getClassFromClassName( $type, $class );				//  try to resolve extended class to object
						if( !$foundClass )															//  extended class is known
							continue;
						$parameter->setType( $foundClass );
						$receivedBy[$foundClass->getId()][]	= $class;
						
					}
					foreach( $method->getParameters() as $parameter )
					{
						if( !$parameter->getCast() )
							continue;
						$type	= $parameter->getCast();
						$foundClass	= $data->getClassFromClassName( $type, $class );				//  try to resolve extended class to object
						if( !$foundClass )															//  extended class is known
							continue;
						$parameter->setCast( $foundClass );
					}
					if( $method->getReturn() )
					{
						$type	= $method->getReturn()->getType();
						$foundClass	= $data->getClassFromClassName( $type, $class );				//  try to resolve extended class to object
						if( !$foundClass )															//  extended class is known
							continue;
						$method->getReturn()->setType( $foundClass );
						$returnedBy[$foundClass->getId()][]	= $class;
					}
				}
			}
		}

		foreach( $data->getFiles() as $fileName => $file )
		{
			foreach( $file->getClasses() as $class )
			{
				foreach( $class->getMethods() as $method )
				{
					if( !$parameter->getType() )
						continue;
					if( $parameter->getCast() == "Model_Interface" )
						die( "!!!!" );
				}

				$classId	= $class->getId();

				if( $class instanceof Model_Class )
				{
					if( array_key_exists( $classId, $extendedBy ) )
						$class->setExtendingClass( $extendedBy[$classId] );
					if( array_key_exists( $classId, $usedBy ) )
						foreach( $usedBy[$classId] as $usingClass )
							$class->setUsingClass( $usingClass );
					if( array_key_exists( $classId, $composedBy ) )
						foreach( $composedBy[$classId] as $composingClass )
							$class->setComposingClass( $composingClass );
					if( array_key_exists( $classId, $receivedBy ) )
						foreach( $receivedBy[$classId] as $receivingClass )
							$class->setReceivingClass( $receivingClass );
					if( array_key_exists( $classId, $returnedBy ) )
						foreach( $returnedBy[$classId] as $returningClass )
							$class->setReturningClass( $returningClass );
				}
				else if( $class instanceof Model_Interface )
				{
					if( array_key_exists( $classId, $extendedBy ) )
						$class->setExtendingInterface( $extendedBy[$classId] );
					if( array_key_exists( $classId, $implementedBy ) )
						foreach( $implementedBy[$classId] as $imlementingClass )
							$class->setImlementingClass( $imlementingClass );
					if( array_key_exists( $classId, $receivedBy ) )
						foreach( $receivedBy[$classId] as $receivingClass )
							$class->setReceivingClass( $receivingClass );
					if( array_key_exists( $classId, $returnedBy ) )
						foreach( $returnedBy[$classId] as $returningClass )
							$class->setReturningClass( $returningClass );
				}
			}
		}
	}	

}
?>