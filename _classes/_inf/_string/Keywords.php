<?
	// ================================================================================= //
	
	class Keywords
	{
		// == CLASS FIELDS ============================================================= //
		
		var $keyword_pattern = '/"(.*?)"|(\w+)/';
		var $keyword_alphanumeric = "[^A-Za-z0-9]";
		
		var $tags = array();
		
		// == CONSTRUCTOR ============================================================== //
		
		function Keywords() {}
		
		// == PARSE KEYWORDS =========================================================== //
		
		/**
		 * Takes a String (which is basically a space delimited array) of words with
		 * special characters and turns them into alpha numeric tags.
		 *
		 * @param String $keyword_input
		 * @return String[]
		 */
		function GetTags($keyword_input)
		{
			// Use pattern matching to suck the tokens out of the input
			// string properly
			
			preg_match_all($this->keyword_pattern, $keyword_input, $keywords);
			$verbose_array = $keywords[0];
			
			// then loop through the verbose array and take out all the non
			// [a-z][A-Z][0-9] chars innit
			
			for ($i=0; $i < sizeof($verbose_array); $i++)
			{
				$this->tags[] = new Tag('', trim(str_replace("\"", "", $verbose_array[$i])), trim(ereg_replace($this->keyword_alphanumeric, "", $verbose_array[$i])));
			}
			
			return $this->tags;
		}
		
		// == TO STRING ================================================================ //
		
		/**
		 * Output the tags array.
		 */
		function ToString() { print_r($this->tags); }
		
		// ============================================================================= //
	}

	// ================================================================================= //
?>