<?
	// ====================================================================== //
	
	class BaseConfig
	{
		// ================================================================== //
		
		var $debug;
		var $file;
		
		// ================================================================== //
		
		/**
		 * Sets the client and loads the correct config set.
		 */
		function __construct($file='', $called_explicitly=true, $debug=0)
		{
			session_start();
			
			if ($called_explicitly === true) echo "You should use Config::GetInstance() instead of new Config()";
			$this->SetClient();
			$this->LoadVars();
			$this->debug = $debug;
			$this->file = $file;
			$_SESSION['config'] = $this;
		}
		
		function GetInstance()
		{
			if (empty($_SESSION['config'])) { return new Config('', false); }
			else return $_SESSION['config'];
		}
		
		// ================================================================== //

		/**
		 * Loads a file assuming that the $file variable is the absolute path. If the
		 * Config->debug is set then it will output a trace of it's activity.
		 *
		 * @param String $file
		 */
		function LoadFile($file)
		{
			if (!empty($this->debug)) echo "Loading: " . $file;
			require_once $file;
			if (!empty($this->debug)) echo "... loaded.<br />";
		}
		
		// ================================================================== //
		
		/**
		 * Checks the HTTP_USER_AGENT and sets the client value.
		 */
		function SetClient()
		{
//			switch (true)
//			{
//				case (strpos($_SERVER['HTTP_USER_AGENT'], "Windows CE")):
//					$this->app['client'] = "PDA";
//					break;
//				
//				case (strpos($_SERVER['HTTP_USER_AGENT'], "PlayStation Portable")):
//					$this->app['client'] = "PSP";
//					break;
//					
//				case (strpos($_SERVER['HTTP_USER_AGENT'], "iPhone")):
//					$this->app['client'] = "IPHONE";
//					break;
//					
//				default:
//					$this->app['client'] = "PC";
//					break;
//			}
			
			switch (true)
			{
				case (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")):		$this->client['browser'] = "ie";		break;
				case (strpos($_SERVER['HTTP_USER_AGENT'], "Firefox")):	$this->client['browser'] = "firefox";	break;
				case (strpos($_SERVER['HTTP_USER_AGENT'], "Chrome")):	$this->client['browser'] = "chrome";	break;
				case (strpos($_SERVER['HTTP_USER_AGENT'], "Safari")):	$this->client['browser'] = "safari";	break;
				case (strpos($_SERVER['HTTP_USER_AGENT'], "pera")):		$this->client['browser'] = "opera";		break;	
				default:												$this->client['browser'] = "unknown";	break;
			}
			
			switch (true)
			{
				case (strpos($_SERVER['HTTP_USER_AGENT'], "Windows")):		$this->client['os'] = "windows";	break;
				case (strpos($_SERVER['HTTP_USER_AGENT'], "Macintosh")):	$this->client['os'] = "osx";		break;
				case (strpos($_SERVER['HTTP_USER_AGENT'], "Linux")):		$this->client['os'] = "linux";		break;
				default:													$this->client['os'] = "unknown";	break;
			}
		}
		
		// ================================================================== //

		/**
		 * Loads the configuration value set based on the HTTP_HOST value.
		 */
		function LoadVars()
		{
			switch ($_SERVER['HTTP_HOST'])
			{
				default:
					$this->DefaultConfig();
					break;
			}
			
			$this->ParseYml();
			$this->SetClient();
			$this->page['referer'] = $_SERVER['HTTP_REFERER'];
			$this->page['current'] = $this->GetCurrentPage();
		}
		
		// ================================================================== //
		
		function ParseYml()
		{
			$array = sfYaml::load('_config/settings.yml');
			foreach ($array['default'] as $config_set_name => $config_set_values) $this->{$config_set_name} = $config_set_values;
			if (!empty($array[$this->server['environment']]))
			{
				foreach ($array[$this->server['environment']] as $config_set_name => $config_set_values)
				{
					foreach ($config_set_values as $config_set_value_name => $config_set_value_value)
					{
						$this->{$config_set_name}[$config_set_value_name] = $config_set_value_value;
					}
				}
			}
		}
		
		// ================================================================== //

		/**
		 * Gets the URL of the current page.
		 *
		 * @return String
		 */
		function GetCurrentPage()
		{
			return "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		
		// ================================================================== //
		
		function LoadDirFiles($directory='')
		{
			$ignore_arr = array(".", "..", ".svn", "_mapping", "_validators", "_core", ".DS_Store");
			if ($handle = opendir($directory))
			{
				while (false !== ($file = readdir($handle)))
				{
					if (!in_array($file, $ignore_arr))
					{
						if (is_dir($directory . "/" . $file)) $this->LoadDirFiles($directory . "/" . $file);
						else $this->LoadFile($directory . "/" . $file);
					}
				}
			}

			closedir($handle);
		}
		
		// ================================================================== //
		
		function LoadOrqi()
		{
			$this->LoadDirFiles($this->classes['inf'] . "/_core");
			$this->LoadDirFiles($this->classes['inf']);
		}
		
		// ================================================================== //
		
		function LoadProject()
		{
			$this->LoadDirFiles($this->base['domain']);
			$this->LoadDirFiles($this->classes['domain']);

			$this->LoadDirFiles($this->base['mapper']);
			$this->LoadDirFiles($this->classes['mapper']);

			$this->LoadDirFiles($this->base['validator']);
			$this->LoadDirFiles($this->classes['validator']);

			foreach ($this->filters as $filter) $this->LoadDirFiles($this->classes['filter'] . "/" . strtolower($filter));
		}
		
		// ================================================================== //
		
		function LoadTestFiles()
		{
			$this->LoadDirFiles($this->classes['test'] . '/' . $this->base['domain']);
			$this->LoadDirFiles($this->classes['test'] . '/' . $this->classes['domain']);

			$this->LoadDirFiles($this->classes['test'] . '/' . $this->base['mapper']);
			$this->LoadDirFiles($this->classes['test'] . '/' . $this->classes['mapper']);

			$this->LoadDirFiles($this->classes['test'] . '/' . $this->base['validator']);
			$this->LoadDirFiles($this->classes['test'] . '/' . $this->classes['validator']);
			
			// TODO include filter tests somewhere
		}
		
		// ================================================================== //
		
		function apacheModuleIsLoaded($mod_name)
		{
			$modules = apache_get_modules();
			return in_array($mod_name, $modules);
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>