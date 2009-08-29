<?
	// ================================================================================= //
	
	class SelectBox
	{
		// ============================================================================= //

		var $control_name;
		var $css_id;
		var $css_class;
		var $value;
		
		var $items = array();
		
		// ============================================================================= //
		
		function SelectBox($config, $object_name, $control_name, $css_id, $css_class, $value, $filter='', $default_note='')
		{
			if (!empty($config))
			{
				$object = new $object_name();
				$this->items = $object->GetSelectBoxData($filter);
			}
			else
			{
				$this->items = array(
					array('value' => '1', 'text' => 'Yes'),
					array('value' => '0', 'text' => 'No')
				);
			}
			
			$this->default_note = (empty($default_note)) ? "Please Choose" : $default_note;
			$this->control_name = $control_name;
			$this->css_id = $css_id;
			$this->css_class = $css_class;
			$this->value = $value;
			
			$this->Render();
		}
		
		// ============================================================================= //
		
		function Render()
		{
			echo "
				<select name='" . $this->control_name . "' id='" . $this->css_id . "' class='" . $this->css_class . "'>
					<option value=''>" . $this->default_note . "</option>
					<option value=''></option>
			";
			
			foreach ($this->items as $item)
			{
				$selected_text = ($item['value'] == $this->value) ? " selected" : "";
				echo "<option value='" . $item['value'] . "'" . $selected_text . ">" . $item['text'] . "</option>";
			}
			
			echo "
				</select>
			";
		}
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>