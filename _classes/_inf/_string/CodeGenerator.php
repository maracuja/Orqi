<?
	// ================================================================================= //
	
	class CodeGenerator
	{
		// ============================================================================= //

		/**
		 * Returns a String based on character substitution from an md5 encoded microtime
		 * value.
		 *
		 * @return String
		 */
		function GetCode()
		{
			$short_code = "";
			$code_letters	= explode(" ", "A F G H 3 K L 4 N P 6 Q R 7 W 9");
			$hex_values		= explode(" ", "0 1 2 3 4 5 6 7 8 9 a b c d e f");
			
			list($micros, $seconds) = explode(" ", microtime());
			$longcode = $seconds . ($micros * 1000000);
			$longcode = md5($longcode);
			
			for ($i=0; $i < strlen($longcode); $i++) $short_code .= $code_letters[$hex_values[$longcode{$i}]];
			return $short_code;
		}
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>