<?php
	class URLHelpers
	{
		// ================================================================== //
		
		/**
		 * This takes in strings and converts them into a valid Orqi URL based
		 * on the configuration settings.
		 * 
		 * object: is the name of a controller. (controller is added to the name when the
		 * Loader tries looking for the controller.
		 * action: is the name of the controller's function.
		 * params: is basically the extra info needed like ?id=X&page=Y
		 *
		 * @param String $object
		 * @param String $action
		 * @param String $params
		 * @return ValidURL
		 */
		function MakeLink($object='', $action='', $params='', $secure=false)
		{
			$this->config = Config::GetInstance();
			$separator = "?";
			
			if (empty($object) && empty($action)) $link_text = $this->config->app['location'] . '/';
			else if (!empty($object) && empty($action)) $link_text = $this->config->app['location'] . '/' . $object;
			else
			{
				if ($this->config->app['mod_rewrite'] == false)
				{
					$site_text = (empty($this->config->app['default'])) ? "" : "site=" . $this->config->app['default'] . "&";
					$link_text = $this->config->app['location'] . "/index.php?" . $site_text . "object=" . $object . "&action=" . $action;
					$separator = "&";
				}
				else
				{
					$site_text = (empty($this->config->app['default'])) ? "" : $this->config->app['default'] . "/";
					$link_text = $this->config->app['location'] . '/' . $site_text . $object . "/" . $action . "." . $this->config->app['extension'];
				}
				
				if ($this->config->app['ssl'] == true && (!empty($_SERVER['HTTPS']) || $secure == true))
				{
					$link_text = str_replace('http:', 'https:', $link_text);
				}
			}
			if (!empty($params)) $link_text .= $separator . $params;
			return $link_text;
		}
		
		// ================================================================== //
		
		/**
		 * This takes in an Orqi URL and replaces the page parameter value with the specified
		 * value.
		 *
		 * @param ValidURL $url
		 * @param Integer $page_number
		 * @return ValidURL
		 */
		function GoToPage($url, $page_number)
		{
			$this->config = Config::GetInstance();
			if ($this->config->app['mod_rewrite'] == false)
			{
				$url = $this->config->app['location'] . "/index.php?object=" . $this->get['object'] . "&action=" . $this->get['action'];
				$separator = "&";
			}
			else
			{
				$url = $this->config->app['location'] . '/' . $this->get['object'] . "/" . $this->get['action'] . "." . $this->config->app['extension'];
				$separator = "?";
			}
			
			$params = "";
			foreach ($this->get as $key => $val)
			{
				if ($key != "object" && $key != "action" && $key != "page")
				{
					$params .= $separator . $key . "=" . $val;
					$separator = "&";
				}
			}
			
			if (!empty($_SERVER['HTTPS']) && $this->config->app['ssl'] == true)
			{
				$url = str_replace('http:', 'https:', $url);
			}
			
			switch (true)
			{
				case (!empty($page_number)):
					$params .= $separator . "page=" . $page_number;
					break;
					
				case (!empty($this->get['page'])):
					$params .= $separator . "page=" . $this->get['page'];
					break;
			}
			return $url . $params;
		}
		
		// ================================================================== //
	}
?>