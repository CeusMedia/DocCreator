<?php
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_B
 */
require_once 'interface/AdvancedDocumentable.php';
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_B
 *	@extends		Test_A
 *	@implements		Test_Interface_AdvancedDocumentable
 *	@author			Hans Wurst
 */
final class Test_C extends Test_A implements Test_Interface_AdvancedDocumentable
{
    /**	@var		Test_B		$b		... */
    public $b;

    /**
     *	@access		public
     *	@param		Test_A		$a		An object of type Test_A
     *	@return		Test_B		An object of type Test_B
     *	@throws		Exception if... erm... like ... erm... never...
     *	@deprecated	you can do better, but this is deprecated
     */
    function doB( Test_A $a )
    {
    	return "no code here";
    }
}
?>