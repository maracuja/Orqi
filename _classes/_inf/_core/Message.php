<?
	// ================================================================================= //		
	
	class Message
	{
		// ============================================================================= //
		
		var $code;
		var $text;
		
		// ============================================================================= //
		
		function Message($code='', $text='')
		{
			$this->code = $code;
			$this->text = $text;
		}
		
		// ============================================================================= //
		
		/**
		 * Get the Code
		 *
		 * @return String
		 */
		function GetCode() { return $this->code; }
		function GetName() { return $this->GetCode(); }
		/**
		 * Get the Text
		 *
		 * @return String
		 */
		function GetText() { return $this->text; }
		
		// ============================================================================= //
		
		/**
		 * Set the Code
		 *
		 * @param String $code
		 */
		function SetCode($code='') { $this->code = $code; }
		/**
		 * Set the Text
		 *
		 * @param String $text
		 */
		function SetText($text='') { $this->text = $text; }
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>