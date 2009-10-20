<?php
/**
 *	@category		cmTools
 *	@package		DocCreator
 */
/**
 *	@category		cmTools
 *	@package		DocCreator
 *	@extends		ArrayObject
 *	@implements		Test
 *	@uses			Test
 *	@uses			Test_A
 */
class Test extends ArrayObject
{
	/** @var	int		$a		Integer 1 */
	public $a	= 1;
	
	/**
	 *	@access		public
	 *	@param		ArrayObject	$a
	 *	@return		void
	 */
	public function __construct( ArrayObject $a, $b, $c = NULL )
	{
	}
}
?>