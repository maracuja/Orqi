<?
	// ================================================================================= //
	
	class BulletGenerator
	{
		// ============================================================================= //
		
		function GenerateList($list='', $css='')
		{
			if (empty($list)) return '';
			$css_string = (empty($css)) ? '' : ' class="' . $css . '"';
			$return_html = "<ul" . $css_string . ">\n";

			$items = explode("\n", $list);
			$counter = 1;
			$last_pipe = "";
			foreach ($items as $item)
			{
				if (trim($item) != '')
				{
					$current_pipe = ($item{0} == "|") ? "|": "";
					
					if ($current_pipe == "|" && $last_pipe == "") { $return_html .= "<ul>\n"; }
					else if ($current_pipe == "" && $last_pipe == "|") { $return_html .= "</ul></li>\n"; }
					else { $return_html .= "</li>\n"; }
					
					$item = str_replace("|", "", $item);
					$return_html .= "<li>" . $item;
					$counter++;
					$last_pipe = $current_pipe;
				}
			}
			
			$return_html .= ($last_pipe == "|") ? "</ul></li>" : "</li>";
			$return_html .= "</ul>\n";
			return $return_html;
		}
		
		// ============================================================================= //
		
		function GenerateNumberedList($list='', $css='')
		{
			if (empty($list)) return '';
			$css_string = (empty($css)) ? '' : ' class="' . $css . '"';
			$return_html = "<ol" . $css_string . ">\n";

			$items = explode("\n", $list);
			$counter = 1;
			$last_pipe = "";
			foreach ($items as $item)
			{
				if (trim($item) != '')
				{
					$current_pipe = ($item{0} == "|") ? "|": "";
					
					if ($current_pipe == "|" && $last_pipe == "") { $return_html .= "<ol>\n"; }
					else if ($current_pipe == "" && $last_pipe == "|") { $return_html .= "</ol></li>\n"; }
					else { $return_html .= "</li>\n"; }
					
					$item = str_replace("|", "", $item);
					$return_html .= "<li>" . $item;
					$counter++;
					$last_pipe = $current_pipe;
				}
			}
			
			$return_html .= ($last_pipe == "|") ? "</ol></li>" : "</li>";
			$return_html .= "</ol>\n";
			return $return_html;
		}
		
		// ============================================================================= //
		
		function GenerateResourceList($resources='', $css='', $link='')
		{
			if (empty($resources)) return '';
			$css_string = (empty($css)) ? '' : ' class="' . $css . '"';
			$return_html = "<ul" . $css_string . ">\n";
			$config = Config::GetInstance();
			
			foreach ($resources as $resource)
			{
				$resource = Caster::Cast($resource, new Resource());
				$resource_type = $resource->GetResourceType();
				$return_html .= "<li class='resource'><a href='$link' onfocus='this.blur();' class='" . $resource_type->GetCssname() . "'>" . $resource->GetName() . "</a></li>\n";
			}
			
			$return_html .= "</ul>\n";
			return $return_html;
		}
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>