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
 *	@version		$Id: Relations.php5 740 2009-10-24 00:04:50Z christian.wuerker $
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
 *	@version		$Id: Relations.php5 740 2009-10-24 00:04:50Z christian.wuerker $
 */
class Reader_Plugin_Relations extends Reader_Plugin_Abstract
{
	protected $extendedBy		= array();
	protected $implementedBy	= array();
	protected $usedBy			= array();
	protected $composedBy		= array();
	protected $receivedBy		= array();
	protected $returnedBy		= array();

	/**
	 *	Collects Relations between Classes.
	 *	@access		protected
	 *	@param		ADT_PHP_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( ADT_PHP_Container $data )
	{
		if( $this->verbose )
			remark( "Finding Class Relations..." );

		foreach( $data->getFiles() as $fileName => $file )
			foreach( $file->getClasses() as $class )
				$this->tryToResolveClassRelations( $data, $class );

		foreach( $data->getFiles() as $fileName => $file )
			foreach( $file->getClasses() as $class )
				$this->setFoundClassRelations( $class );
	}	

	protected function setFoundClassRelations( $class )
	{
/*		foreach( $class->getMethods() as $method )
		{
			if( !$parameter->getType() )
				continue;
			if( $parameter->getCast() == "ADT_PHP_Interface" )
				die( "!!!!" );
		}
*/
		$classId	= $class->getId();

		if( $class instanceof ADT_PHP_Class )
		{
			if( array_key_exists( $classId, $this->extendedBy ) )
				$class->setExtendingClass( $this->extendedBy[$classId] );
			if( array_key_exists( $classId, $this->usedBy ) )
				foreach( $this->usedBy[$classId] as $usingClass )
					$class->setUsingClass( $usingClass );
			if( array_key_exists( $classId, $this->composedBy ) )
				foreach( $this->composedBy[$classId] as $composingClass )
					$class->setComposingClass( $composingClass );
			if( array_key_exists( $classId, $this->receivedBy ) )
				foreach( $this->receivedBy[$classId] as $receivingClass )
					$class->setReceivingClass( $receivingClass );
			if( array_key_exists( $classId, $this->returnedBy ) )
				foreach( $this->returnedBy[$classId] as $returningClass )
					$class->setReturningClass( $returningClass );
		}
		else if( $class instanceof ADT_PHP_Interface )
		{
			if( array_key_exists( $classId, $this->extendedBy ) )
				$class->setExtendingInterface( $this->extendedBy[$classId] );
			if( array_key_exists( $classId, $this->implementedBy ) )
				foreach( $this->implementedBy[$classId] as $imlementingClass )
					$class->setImlementingClass( $imlementingClass );
			if( array_key_exists( $classId, $this->receivedBy ) )
				foreach( $this->receivedBy[$classId] as $receivingClass )
					$class->setReceivingClass( $receivingClass );
			if( array_key_exists( $classId, $this->returnedBy ) )
				foreach( $this->returnedBy[$classId] as $returningClass )
					$class->setReturningClass( $returningClass );
		}
	}

	protected function tryToResolveClassRelations( $data, $class )
	{
		if( $class->getUsedClasses() )																//  current class uses other classes
		{
			foreach( $class->getUsedClasses() as $nr => $className )								//  iterate used classes
			{
				try
				{
					$usedClass	= $data->getClassFromClassName( $className, $class );				//  try to resolve class to object
					$this->usedBy[$usedClass->getId()][]	= $class;									//  note the resolved class uses current class
					$class->setUsedClass( $usedClass );												//  store resolved class object instead of class name string
				}
				catch( Exception $e ){}
			}
		}

		if( $class->getImplementedInterfaces() )													//  current class implements interfaces
		{
			foreach( $class->getImplementedInterfaces() as $nr => $className )						//  iterate interfaces
			{
				try
				{
					$implementedClass	= $data->getClassFromClassName( $className, $class );		//  try to resolve interface to object
					$this->implementedBy[$implementedClass->getId()][]	= $implementedClass;		//  note that resolved interface is implemented by currend class
					$class->setImplementedInterface( $implementedClass );							//  store resolved interface object instead of interface name string
				}
				catch( Exception $e ){}
			}
		}

		if( $class->getExtendedClass() )															//  current class is extending another class
		{
			$superClass		= $class->getExtendedClass();
			try
			{
				$extendedClass	= $data->getClassFromClassName( $superClass, $class );				//  try to resolve extended class to object
				$this->extendedBy[$extendedClass->getId()]	= $extendedClass;						//  note that extended class is extended by current class
				$class->setExtendedClass( $extendedClass );											//  store resolved class object instead of class name string
			}
			catch( Exception $e ){}
		}

		foreach( $class->getMembers() as $member )
		{
			if( !$member->getType() )
				continue;
			try
			{
				$foundClass	= $data->getClassFromClassName( $member->getType(), $class );				//  try to resolve extended class to object
				$member->setType( $foundClass );
				$this->composedBy[$foundClass->getId()][]	= $foundClass;
			}
			catch( Exception $e ){}
		}

		foreach( $class->getMethods() as $method )
		{
			foreach( $method->getParameters() as $parameter )
			{
				if( !$parameter->getType() )
					continue;
				$type	= $parameter->getType();													//  get type of parameter
				try
				{
					$foundClass	= $data->getClassFromClassName( $type, $class );					//  try to resolve extended class to object
					$parameter->setType( $foundClass );
					$this->receivedBy[$foundClass->getId()][]	= $class;
				}
				catch( Exception $e ){}
			}

			foreach( $method->getParameters() as $parameter )
			{
				if( !$parameter->getCast() )
					continue;
				$type	= $parameter->getCast();
				try
				{
					$foundClass	= $data->getClassFromClassName( $type, $class );					//  try to resolve extended class to object
					$parameter->setCast( $foundClass );
				}
				catch( Exception $e ){}
			}

			if( $method->getReturn() )
			{
				$type	= $method->getReturn()->getType();
				try
				{
					$foundClass	= $data->getClassFromClassName( $type, $class );					//  try to resolve extended class to object
					$method->getReturn()->setType( $foundClass );
					$this->returnedBy[$foundClass->getId()][]	= $class;
				}
				catch( Exception $e ){}
			}
		}
	}
}
?>