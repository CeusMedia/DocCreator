<?php
/**
 *	@category		cmTools
 *	@package		DocCreator.test.interface
 *	@version		$Id$
 */
require_once 'ExtendedDocumentable.php';
/**
 *	@category		cmTools
 *	@package		DocCreator.test.interface
 *	@extends		Interface_ExtendedDocumentable
 *	@version		$Id$
 */
interface Test_Interface_AdvancedDocumentable extends Interface_ExtendedDocumentable
{
	public function toDoc();
}
?>