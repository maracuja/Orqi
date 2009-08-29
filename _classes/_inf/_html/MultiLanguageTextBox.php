<?
	// ================================================================================= //
	
	class MultiLanguageTextBox
	{
		// ============================================================================= //
		
		function Render($name='', $values='')
		{
			$tabs = "";
			$tabs_content = "";
			$selected = "class='selected'";
			$session = new Session();

			foreach ($session->languages as $language)
			{
				$language = Caster::Cast($language, new Language());
				$tabs .= "<li $selected><a href='#" . $name . "_" . $language->GetId() . "'><em>" . strtolower($language->GetEnglishName()) . "</em></a></li>\n";
				$tabs_content .= "
					<div id='" . $name . "_" . $language->GetId() . "'>
						<input type='text' name='" . $name . "_" . $language->GetId() . "' value='" . $values->GetText($language) . "' />
					</div>
				";
			}
			
			return "
				<div id='demo' class='yui-navset'>
					<ul class='yui-nav'>
						$tabs
					</ul>
					<div class='yui-content'>
						$tabs_content
					</div>
				</div>
			";
		}

		// ============================================================================= //
	}
	
	// ================================================================================= //
?>