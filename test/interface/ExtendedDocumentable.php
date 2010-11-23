<?php
/**
 *	@category		cmTools
 *	@package		DocCreator.Test.Interface
 *	@version		$Id$
 */
require_once 'Documentable.php';
/**
 *	@category		cmTools
 *	@package		DocCreator.Test.Interface
 *	@extends		Interface_Documentable
 *	@version		$Id$
 */
interface Test_Interface_ExtendedDocumentable extends Test_Interface_Documentable
{
	public function toDoc();
	
	/**
	 *	@param		Interface_Documentable		$interface		An Interface
	 *	@return		Interface_AdvancedDocumentable
	 */
	public function trySomething( Interface_Documentable $interface );
	
}
?>