<?php
/**
 *	@category		cmTools
 *	@package		DocCreator.Test.Interface
 *	@version		$Id$
 */
require_once 'ExtendedDocumentable.php';
/**
 *	@category		cmTools
 *	@package		DocCreator.Test.Interface
 *	@extends		Interface_ExtendedDocumentable
 *	@version		$Id$
 */
interface Test_Interface_AdvancedDocumentable extends Test_Interface_ExtendedDocumentable
{
	public function toDoc();
}
?>