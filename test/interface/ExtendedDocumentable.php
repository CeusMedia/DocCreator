<?php
/**
 *	@category		cmTools
 *	@package		DocCreator.test.interface
 *	@version		$Id$
 */
require_once 'Documentable.php';
/**
 *	@category		cmTools
 *	@package		DocCreator.test.interface
 *	@extends		Interface_Documentable
 *	@version		$Id$
 */
interface Interface_ExtendedDocumentable extends Interface_Documentable
{
	public function toDoc();
	
	/**
	 *	@param		Interface_Documentable		$interface		An Interface
	 *	@return		Interface_AdvancedDocumentable
	 */
	public function trySomething( Interface_Documentable $interface );
	
}
?>