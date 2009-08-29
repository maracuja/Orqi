<?
	// ================================================================================= //
	
	class Breadcrumblink
	{
		// ============================================================================= //

		var $link;
		var $label;

		// ============================================================================= //

		function Breadcrumblink($link='', $label='')
		{
			$this->link = $link;
			$this->label = $label;
		}
		
		// ============================================================================= //
		
		/**
		 * Get the Link
		 *
		 * @return String
		 */
		function GetLink()
		{
			return $this->link;
		}
		/**
		 * Get the Label
		 *
		 * @return String
		 */
		function GetLabel()
		{
			return $this->label;
		}
				
		// ============================================================================= //
		
		/**
		 * Set the Link
		 *
		 * @param String $link
		 */
		function SetLink($link='') { $this->link = $link; }
		/**
		 * Set the Label
		 *
		 * @param String $label
		 */
		function SetLabel($label='') { $this->label = $label; }

		// ============================================================================= //
		
		/**
		 * Outputs the Breadcrumblink as an HTML anchor tag with an optional css style
		 * value. 
		 *
		 * @param String $style_name
		 * @return String
		 */
		function Render($style_name='')
		{
			$style_html = (empty($style_name)) ? '' : " class='" . $style_name . "'";
			
			if (empty($this->link)) $link_html = "<span" . $style_html. ">" . $this->label . "</span>";
			else $link_html = "<a href='" . $this->link . "'" . $style_html. ">" . $this->label . "</a>";
			return $link_html;
		}
		
		// ============================================================================= //
	}
	
	// ================================================================================= //

?>