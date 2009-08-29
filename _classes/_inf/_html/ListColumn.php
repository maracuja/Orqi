<?
	// ====================================================================== //
	
	class ListColumn
	{
		function ListColumn($label='', $name='', $width='', $default='')
		{
			$this->label = $label;
			$this->name = $name;
			$this->width = $width;
			$this->default = $default;
		}
		
		function GetLabel() { return $this->label; }
		function GetName() { return $this->name; }
		function GetWidth() { return $this->width; }
	}

	// ====================================================================== //
?>