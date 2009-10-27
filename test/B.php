<?php
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_B
 */
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_B
 *	@author			Hans Wurst
 */
final class Test_B extends Test_A
{
    /**	@var		Test_B		$b		... */
    public $b;

    /**
     *	@access		public
     *	@param		Test_A		$a		An object of type Test_A
     *	@return		Test_B		An object of type Test_B
     *	@throws		Exception if... erm... like ... erm... never...
     *	@deprecated		you can do better, but this is deprecated
     */
    function doB( Test_A $a )
    {
    	return "no code here";
    }
}
?>