<?
	// ====================================================================== //
	
	class Config extends BaseConfig
	{
		// ================================================================== //

		function LoadVars()
		{
			switch (true)
			{
				case (strpos($_SERVER['DOCUMENT_ROOT'], "carl")):
					$this->server['environment'] = "carl";
					break;
					
				case ($_SERVER['HTTP_HOST'] == "skirmish.dev451.com"):
					$this->server['environment'] = "dev";
					break;

				case (strpos($_SERVER['HTTP_HOST'], "92.168.")):
					$this->server['environment'] = "virtual";
					break;
					
				case ($_SERVER['HTTP_HOST'] == "localhost:8080"):
					$this->server['environment'] = "mbp";
					break;
					
				default:
					$this->server['environment'] = "local";
					break;
			}
			
			$this->ParseYml();
			$this->page['referer'] = $_SERVER['HTTP_REFERER'];
			$this->page['current'] = $this->GetCurrentPage();
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>