<?php
/**
 *	Collects Relations between Classes.
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
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Reader\Plugin;

use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\Container_ as PhpContainer;
use CeusMedia\PhpParser\Structure\Interface_ as PhpInterface;

use Exception;

/**
 *	Collects Relations between Classes.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Relations extends Abstraction
{
	/**
	 *	Collects Relations between Classes.
	 *	@access		protected
	 *	@param		PhpContainer	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( PhpContainer $data )
	{
		if( $this->verbose )
			$this->env->out->sameLine( "Plugin: Class/Interface Relations" );

		foreach( $data->getFiles() as $fileName => $file ){
			foreach( $file->getClasses() as $class )
				if( $class instanceof PhpClass )
					$this->tryToResolveClassRelations( $data, $class );
			foreach( $file->getInterfaces() as $interface )
				$this->tryToResolveInterfaceRelations( $data, $interface );
		}
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		PhpContainer	$data			Object containing collected Class Data
	 *	@param		PhpClass		$class			Class Data Object
	 *	@return		void
	 */
	protected function tryToResolveClassRelations( PhpContainer $data, PhpClass $class )
	{
		if( $class->getUsedClasses() ){																//  current class uses other classes
			foreach( $class->getUsedClasses() as $nr => $className ){								//  iterate used classes
				try{
					$usedClass	= $data->getClassFromClassName( $className, $class );				//  try to resolve class to object
					$usedClass->setUsingClass( $class );
					$class->setUsedClass( $usedClass );												//  store resolved class object instead of class name string
				}
				catch( Exception $e ){}
			}
		}

		if( $class->getImplementedInterfaces() ){													//  current class implements interfaces
			foreach( $class->getImplementedInterfaces() as $nr => $interfaceName ){					//  iterate interfaces
				try{
					$implementedInterface	= $data->getInterfaceFromInterfaceName( $interfaceName, $class );		//  try to resolve interface to object
					$implementedInterface->setImplementingClass( $class ) ;
					$class->setImplementedInterface( $implementedInterface );							//  store resolved interface object instead of interface name string
				}
				catch( Exception $e ){}
			}
		}

		if( $class->getExtendedClass() ){															//  current class is extending another class
			$superClass		= $class->getExtendedClass();
			try{
				$extendedClass	= $data->getClassFromClassName( $superClass, $class );				//  try to resolve extended class to object
				$extendedClass->setExtendingClass( $class );
				$class->setExtendedClass( $extendedClass );											//  store resolved class object instead of class name string
			}
			catch( Exception $e ){}
		}

		foreach( $class->getMembers() as $member ){
			if( !$member->getType() )
				continue;
			try{
				$foundClass	= $data->getClassFromClassName( $member->getType(), $class );				//  try to resolve extended class to object
				$member->setType( $foundClass );
				$foundClass->setComposingClass( $class );
			}
			catch( Exception $e ){}
		}
		$this->tryToResolveMethodRelations( $data, $class );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		PhpContainer	$data			Object containing collected Class Data
	 *	@param		PhpInterface	$interface		...
	 *	@return		void
	 */
	protected function tryToResolveInterfaceRelations( PhpContainer $data, PhpInterface $interface )
	{
		if( $interface->getExtendedInterface() ){													//  current interface is extending another interface
			$parent		= $interface->getExtendedInterface();
			try{
				$extendedInterface	= $data->getInterfaceFromInterfaceName( $parent, $interface );	//  try to resolve extended interface to object
				$extendedInterface->setExtendingInterface( $interface );
				$interface->setExtendedInterface( $extendedInterface );								//  store resolved interface object instead of interface name string
			}
			catch( Exception $e ){}
		}
		$this->tryToResolveMethodRelations( $data, $interface );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		PhpContainer	$data			Object containing collected Class Data
	 *	@param		PhpInterface	$artefact		Interface or Class
	 *	@return		void
	 */
	protected function tryToResolveMethodRelations( PhpContainer $data, PhpInterface $artefact )
	{
		foreach( $artefact->getMethods() as $method ){
			foreach( $method->getParameters() as $parameter ){
				if( !$parameter->getType() )
					continue;
				$type	= $parameter->getType();													//  get type of parameter
				try{
					if( $artefact instanceof PhpClass ){
						$foundClass	= $data->getClassFromClassName( $type, $artefact );				//  try to resolve extended Class to object
						$foundClass->addReceivingClass( $artefact );
					}
					else if( $artefact instanceof PhpInterface ){
						$foundClass	= $data->getInterfaceFromInterfaceName( $type, $artefact );		//  try to resolve extended Interface to object
						$foundClass->addReceivingInterface( $artefact );
					}
					$parameter->setType( $foundClass );
				}
				catch( Exception $e ){}
			}

			foreach( $method->getParameters() as $parameter ){
				if( !$parameter->getCast() )
					continue;
				$type	= $parameter->getCast();
				try{
					if( $artefact instanceof PhpClass )
						$foundClass	= $data->getClassFromClassName( $type, $artefact );				//  try to resolve extended Class to object
					else if( $artefact instanceof PhpInterface )
						$foundClass	= $data->getInterfaceFromInterfaceName( $type, $artefact );		//  try to resolve extended Interface to object

					$parameter->setCast( $foundClass );
				}
				catch( Exception $e ){}
			}

			if( $method->getReturn() ){
				$type	= $method->getReturn()->getType();
				try{
					if( $artefact instanceof PhpClass ){
						$foundClass	= $data->getClassFromClassName( $type, $artefact );				//  try to resolve extended Class to object
						$foundClass->addReturningClass( $artefact );
					}
					else if( $artefact instanceof PhpInterface ){
						$foundClass	= $data->getInterfaceFromInterfaceName( $type, $artefact );		//  try to resolve extended Interface to object
						$foundClass->addReturningInterface( $artefact );
					}
					$method->getReturn()->setType( $foundClass );
				}
				catch( Exception $e ){}
			}
		}
	}
}
