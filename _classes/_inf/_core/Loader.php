<?
	// ====================================================================== //

	class Loader
	{
		// ================================================================== //

		var $config;
		var $session;
		
		var $get;
		var $post;
		var $files;
		
		var $CONTROLLER;
		var $ACTION;
		var $ID;
		
		var $loaded;
		var $error_message;
		
		// ================================================================== //
		
		/**
		 * This is the Loader constructor. It basically sets some basic settings
		 * takes in the _GET, _POST and _FILES variables and such.
		 *
		 * @param _GET $get
		 * @param _POST $post
		 * @param _FILES $files
		 */
		function Loader($get, $post, $files)
		{
			if ($get['object'] == "pages")
			{
				$get['object'] = "page";
				$get['id'] = $get['action'];
				$get['action'] = "view";
			}
			
			$this->config			= Config::GetInstance();
			
			// blank screen of death? TAKE $this->session = new Session() out of
			// the Users constructor ffs
			
			$this->get				= $get;
			$this->post				= $post;
			$this->files			= $files;

			$this->loaded			= false;
			$this->error_message	= "";
			
			$this->CONTROLLER		= (empty($get['object'])) ? "" : ucwords(strtolower($get['object'])) . "Controller";
			$this->ACTION			= ucwords(strtolower($get['action']));
			$this->ID				= $get['id'];
		}
		
		// ================================================================== //
		
		/**
		 * This will check for the existence of the called controller by appending
		 * "Controller" to the CONTROLLER variable value. If the controller file is
		 * found it is loaded and then checked for a function which corresponds to
		 * the ACTION value. If both checks pass then returns TRUE otherwise returns
		 * FALSE.
		 * 
		 * TODO move the default CONTROLLER and ACTION values into the Config class
		 *
		 * @return Boolean
		 */
		function VerifyController()
		{
			if ($this->CONTROLLER == "" && $this->ACTION == "")
			{
				$this->CONTROLLER	= "PageController";
				$this->ACTION		= "Home";
			}
			
			// Check that the parameters are well-formed			
			if ($this->CONTROLLER == "")
			{
				$this->error_message = "Error: Class name value was blank.";
				$this->loaded = false;
				return $this->loaded;
			}
			
			if ($this->ACTION == "")
			{
				$this->error_message = "Error: Class method name value was blank.";
				$this->loaded = false;
				return $this->loaded;
			}
			
			// Check that the data class file actually exists
			$this->CheckFile($this->config->base['app'] . '/Base' . $this->CONTROLLER . ".php", "Base" . $this->CONTROLLER);
			$this->CheckFile($this->config->classes['app'] . '/' . $this->CONTROLLER . ".php", $this->CONTROLLER);

			// Check the function we want to run is in the class
			if (is_callable(array($this->CONTROLLER, $this->ACTION)))
			{
				$this->error_message = "";
				$this->loaded = true;
			}
			else
			{
				echo $this->CONTROLLER . "->" . $this->ACTION;
				header("HTTP/1.0 404 Not Found"); exit;
				// $this->error_message = "Error: Class " . $this->CONTROLLER . " does not contain a method called '" . $this->ACTION . "'.";
				$this->loaded = false;
			}

			return $this->loaded;
		}
		
		// ================================================================== //
		
		/**
		 * If the VerifyController call has returned TRUE then that means the
		 * controller is available and it contains the requested function. Execute
		 * simply instantiates the controller, calls the Initialise() (which is
		 * in the super-class) and then finally the requested action.
		 */
		function Execute()
		{
			$controller = new $this->CONTROLLER();
			$controller->Initialise($this->get, $this->post, $this->files);
			$controller->StartFilters();
			$controller->{$this->ACTION}();
			$controller->StopFilters();
		}
		
		// ================================================================== //
		
		/**
		 * Takes in a file location and a class name. First we check for the existence
		 * of the file, then we check that it contains the class we are looking for.
		 * If both conditions are true we return TRUE, otherwise we return FALSE.
		 *
		 * @param String $path
		 * @param String $class_name
		 * @return Boolean
		 */
		function CheckFile($path, $class_name)
		{
			// Check that the data class file actually exists
			if (file_exists($path))
			{
				require_once $path;
				$chk1 = true;
			}
			else
			{
				$this->error_message = "Error: Class " . $path . " not found.";
				$chk1 = false;
			}

			// Check the class is actually in the file
			if (!class_exists($class_name))
			{
				$this->error_message = "Error: Class " . $class_name . " not found.";
				$chk2 = false;
			}
			else $chk2 = true;
			
			return ($chk1 && $chk2);
		}
		
		// ================================================================== //

		/**
		 * Deprecated. Don't use.
		 *
		 */
		function DisplayError()
		{
			echo "<div>" . $this->error_message . "</div>";
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>