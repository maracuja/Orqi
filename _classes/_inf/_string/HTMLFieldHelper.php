<?php
	class HTMLFieldHelper
	{
		function Substitution($html='')
		{
			$siteimages = new Siteimages();
			$config = Config::GetInstance();
			
			preg_match_all("/\{(.*?)\:(.*?)\}/", $html, $matches);
			foreach ($matches[2] as $i => $match)
			{
				switch ($matches[1][$i])
				{
					case "img":
						$siteimage = $siteimages->FindByLabel($match);
						if ($siteimage instanceof Siteimage)
						{
							$html = str_replace("{img:$match}", "<img src='" . $config->resource['url'] . $siteimage->GetName() . "' />", $html);
						}
						break;
							
					case "url":
						$siteimage = $siteimages->FindByLabel($match);
						if ($siteimage instanceof Siteimage)
						{
							$html = str_replace("{url:$match}", $config->resource['url'] . $siteimage->GetName(), $html);
						}
						break;
							
					case "link":
						list($controller, $function, $params) = explode(", ", $match);
						$html = str_replace("{link:$match}", $this->MakeLink($controller, $function, $params), $html);
						break;
							
					case "ext:http":
						$html = str_replace("{ext:http:$match}", "http:" . $match, $html);
						break;
						
					case "ext:https":
						$html = str_replace("{ext:https:$match}", "https:" . $match, $html);
						break;
				}
			}
			
			return $html;
		}
	}
?>