<?
	// ====================================================================== //
	
	class RowOption
	{
		function RowOption($label='', $name='', $level='')
		{
			$this->label = $label;
			$this->name = $name;
			$this->level = $level;
		}
		
		function GetLabel() { return $this->label; }
		function GetName() { return $this->name; }
		function GetLevel() { return $this->level; }
	}

	// ====================================================================== //
?>