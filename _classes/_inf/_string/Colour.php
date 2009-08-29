<?
	// ================================================================================= //		
	
	class Colour
	{
		// ============================================================================= //
		
		var $hex_value;
		var $dec_value;
		
		// ============================================================================= //

		/**
		 * Takes in an hexidecimal or decimal colour value which can then
		 * be output as hexidecimal, decimal or percentages.
		 *
		 * @param String $hex
		 * @param String $dec
		 */
		function Colour($hex='', $dec='')
		{
			$this->hex_value = $hex;
			$this->dec_value = $dec;
		}
		
		// ============================================================================= //
		
		/**
		 * Get the Red Hexidecimal value
		 *
		 * @return String
		 */
		function GetRedHex() { return $this->hex_value{0} . $this->hex_value{1}; }
		/**
		 * Get the Green Hexidecimal value
		 *
		 * @return String
		 */
		function GetGreenHex() { return $this->hex_value{2} . $this->hex_value{3}; }
		/**
		 * Get the Blue Hexidecimal value
		 *
		 * @return String
		 */
		function GetBlueHex() { return $this->hex_value{4} . $this->hex_value{5}; }

		/**
		 * Get the Red decimal value
		 *
		 * @return String
		 */
		function GetRedDec() { return hexdec($this->GetRedHex()); }
		/**
		 * Get the Green decimal value
		 *
		 * @return String
		 */
		function GetGreenDec() { return hexdec($this->GetGreenHex()); }
		/**
		 * Get the Blue decimal value
		 *
		 * @return String
		 */
		function GetBlueDec() { return hexdec($this->GetBlueHex()); }

		/**
		 * Get the Red percentage value
		 *
		 * @return String
		 */
		function GetRedPer() { return $this->GetRedDec() / 255; }
		/**
		 * Get the Green percentage value
		 *
		 * @return String
		 */
		function GetGreenPer() { return $this->GetGreenDec() / 255; }
		/**
		 * Get the Blue percentage value
		 *
		 * @return String
		 */
		function GetBluePer() { return $this->GetBlueDec() / 255; }
		
		// ============================================================================= //
		
		/**
		 * Set the hexidecimal value
		 *
		 * @param String $hex
		 */
		function SetHex($hex='') { $this->hex_value = $hex; }
		/**
		 * Set the decimal value
		 *
		 * @param String $dec
		 */
		function SetDec($dec='') { $this->dec_value = $dec; }
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>