<?
	// ====================================================================== //

	class Controller
	{
		// ================================================================== //
		
		var $config;
		var $session;
		
		var $get = array();
		var $post = array();
		
		var $files = array();
		var $goto;
		var $object;
		var $domain_object;
		var $page = array();

		// == PSEUDO CONSTRUCTOR ============================================ //
		
		/**
		 * This function is to make up for the lack of a callable constructor. This
		 * class is meant as a base for the user-generated controllers to extend. They
		 * will all have different names so the Loader needs one function that it can
		 * always call to setup the execution environment.
		 *
		 * @param Config $config
		 * @param Session $session
		 * @param _GET $get
		 * @param _POST $post
		 * @param _FILES $files
		 */
		function Initialise($get, $post, $files)
		{
			$this->config			= Config::GetInstance();
			$this->session			= new Session();
			
			$this->get				= $get;
			$this->post				= $post;
			
			$this->files			= $files;
			$this->goto				= $_SERVER['HTTP_REFERER'];
			$this->domain_object	= ucwords($get['object']);

			if (!function_exists('add_delegate'))
			{
				function add_delegate(&$my_item)
				{
					if (!is_array($my_item))
					{
						if (get_magic_quotes_gpc()) $my_item = stripslashes($my_item);
						$my_item = str_replace("\r", "", trim($my_item));
						$my_item = str_replace("\n\n\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n", "\n\n", $my_item);
				
						$search_for = array('&apos;', '\'', '"', '<', '>');
						$replace_with = array('&#39;', '&#39;', '&quot;', '&lt;', '&gt;');

						$my_item = str_replace($search_for, $replace_with, $my_item);
					}
				}
				
				function strip_delegate(&$my_item)
				{
					if (!is_array($my_item))
					{
						$my_item = str_replace("\r", "", trim($my_item));
						$my_item = str_replace("\n\n\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n", "\n\n", $my_item);
						$my_item = str_replace("\n\n\n", "\n\n", $my_item);
				
						$replace_with = array('\'', '\'', '"', '<', '>');
						$search_for = array('&apos;', '&#39;', '&quot;', '&lt;', '&gt;');
	
						$my_item = str_replace($search_for, $replace_with, $my_item);
					}
				}
			}
			
			$this->InsertSlashes($this->post);
			$this->InsertSlashes($this->get);
		}
		
		// ================================================================== //
		
		/**
		 * This will perform a standard object delete from the database without
		 * taking into account any object relationships or related files. This
		 * is quite a convenient function, because for simple deletes the sub-class
		 * can just call this function.
		 */
		function Delete()
		{
			$object_name = ucwords($this->get['object']);
			$object = new $object_name($this->get['id']);
			$mapper = $object->GetMapper();
			$mapper->Delete($object);
			$this->session->AddMessage(new Message('success', "The " . strtolower($object_name) . " was deleted."));
			$this->Redirect();
		}
		
		// == HELPER FUNCTIONS ============================================== //
		
		/**
		 * Takes in a file name and includes the file. First it looks for the file
		 * in the project _UI folder. If it can't find a file it will look in Orqi's
		 * _UI folder. If it still hasn't found a file it will try using the file
		 * name as on absolute link and if that fails then it outputs an error message. 
		 *
		 * The $sendtoscreen parameter is set to TRUE by default. This means when the
		 * template is included the HTML in it will start being written to the screen.
		 * When $sendtoscreen is set to FALSE, the incoming template is saved as a
		 * string and returned to the caller. This is useful for sending emails and
		 * having email templates setup in the _UI folder.
		 * 
		 * @param String $template_name
		 * @param Boolean $sendtoscreen
		 * @return HTML||false
		 */
		function LoadTemplate($template_name='', $sendtoscreen=true)
		{
			if (!$sendtoscreen) ob_start(); 
			
			switch (true)
			{
				case file_exists($this->config->classes['ui'] . "/" . $template_name . ".php"):
					require $this->config->classes['ui'] . "/" . $template_name . ".php";
					break;
					
				case file_exists($template_name):
					require $template_name;
					break;
					
				default:
					echo "<p>The template wasn't found.</p>";
					break;
			}
			
			if (!$sendtoscreen)
			{
				$return_html = ob_get_contents();
				ob_end_clean();
				return $return_html;
			}
			else return false;
		}
		
		// ================================================================== //
		
		/**
		 * Just like the LoadTemplate() function, this checks for components in the _UI
		 * folders of the project, then Orqi, finally trying the $component_name as an
		 * absolute URL before printing out an error.
		 * 
		 * In some templates I've used local variables (largely because of the limitations
		 * of PHP4) and to allow the component to access these variables, the LoadComponent()
		 * function can be passed them in the $param1-4 spaces.
		 *
		 * NB: $sendtoscreen is not needed here as LoadComponent() inherits that behaviour
		 * NB: Component file names all begin with an underscore.
		 * 
		 * @param String $component_name
		 * @param Object $param1
		 * @param Object $param2
		 * @param Object $param3
		 * @param Object $param4
		 */
		function LoadComponent($component_name='', $param1='', $param2='', $param3='', $param4='')
		{
			if (strpos($component_name, "/"))
			{
				$component_name_parts = explode("/", $component_name);
				$component_name_parts[count($component_name_parts)-1] = "_" . $component_name_parts[count($component_name_parts)-1];
				$component_name = '';
				$separator = '';
				foreach ($component_name_parts as $component_name_part)
				{
					$component_name .= $separator . $component_name_part;
					$separator = "/";
				}
			}
			else $component_name = "_" . $component_name;
			
			switch (true)
			{
				case file_exists($this->config->classes['ui'] . "/" . $component_name . ".php"):
					require $this->config->classes['ui'] . "/" . $component_name . ".php";
					break;
					
				case file_exists($component_name):
					require $component_name;
					break;
					
				default:
					echo "<p>The " . $component_name . " component is missing.</p>";
					break;
			}
		}

		// ================================================================== //
		
		/**
		 * @deprecated See Request object
		 */
		function Redirect($goto='') { Request::Redirect($goto); }
		/**
		 * @deprecated See URLHelpers object
		 */
		function MakeLink($object='', $action='', $params='', $secure=false) { return URLHelpers::MakeLink($object, $action, $params, $secure); }
		/**
		 * @deprecated See URLHelpers object
		 */
		function GoToPage($url='', $page_number='') { return URLHelpers::GoToPage($url, $page_number); }
		
		// ================================================================== //
		
		/**
		 * This is usually called on a list page that displays a paging bar. This
		 * function will create an associative array of values that contain all the
		 * necessary values to work out the links for the paging bar.
		 * 
		 * $page['items']: total number of items
		 * $page['first']: the number of the first page
		 * $page['last']: the number of the last page
		 * $page['prev']: the number of the previous page
		 * $page['next']: the number of the next page
		 * $page['current']: the current page number
		 * $page['start']: the first row to select in the SQL statement, ie LIMIT start, end
		 * $page['end']: the last row to select in the SQL statement, ie LIMIT start, end
		 *
		 * @param Integer $total_rows
		 */
		function SetPagingVars($total_rows='')
		{
			$this->page['items'] = (empty($total_rows) && $total_rows != '0') ? $this->object->GetTotalRows() : $total_rows;
			$this->page['first'] = 1;
			$this->page['last'] = ceil($this->page['items'] / $this->config->page['items']);
			
			if (!empty($this->get['page'])) $this->page['current'] = $this->get['page'];
			if ($this->page['current'] > $this->page['last']) 					$this->page['current'] = $this->page['last'];
			if (empty($this->page['current']) || $this->page['current'] < 1)	$this->page['current'] = 1;
			
			$this->page['next'] = $this->page['current'] + 1;
			$this->page['prev'] = $this->page['current'] - 1;
			
			$this->page['start'] = ($this->page['current']-1) * $this->config->page['items'];
			
			$this->page['end'] = $this->page['start'] + $this->config->page['items'];
			if ($this->page['end'] > $this->page['items']) $this->page['end'] = $this->page['items'];
		}
		
		// ================================================================== //

		/**
		 * Walks through an array, taking out all the slashes and substitutions.
		 *
		 * @param Array $my_array
		 */
		function RemoveSlashes(&$my_array)
		{
			array_walk($my_array, 'strip_delegate');
		}
		
		// ================================================================== //
		
		/**
		 * Walks through an array, substituting special characters like ', ", <, > and &
		 *
		 * @param Array $my_array
		 */
		function InsertSlashes(&$my_array)
		{
			array_walk($my_array, 'add_delegate');
		}
		
		// ================================================================== //
		
		/**
		 * Replaces special characters for safe substitutions and strips unnecessary
		 * white space.
		 *
		 * @param String $my_item
		 * @return String
		 */
		function RenderForHTML($my_item, $new_lines=false)
		{
			$my_item = str_replace("\r", "", trim($my_item));
			$my_item = str_replace("\n\n\n\n\n", "\n\n", $my_item);
			$my_item = str_replace("\n\n\n\n", "\n\n", $my_item);
			$my_item = str_replace("\n\n\n", "\n\n", $my_item);
			$my_item = str_replace("\n\n\n", "\n\n", $my_item);
			$my_item = str_replace("\n\n\n", "\n\n", $my_item);
			
			if ($new_lines) $my_item = nl2br($my_item);
			
			$replace_with = array('\'', '\'', '"', '<', '>');
			$search_for = array('&apos;', '&#39;', '&quot;', '&lt;', '&gt;');

			return str_replace($search_for, $replace_with, $my_item);
		}
		
		// ================================================================== //
		
		/**
		 * Replaces special characters for xml-safe substitutions.
		 *
		 * @param String $my_item
		 * @return String
		 */
		function RenderForXML($my_item)
		{
			$my_item = str_replace("&", "&amp;", $my_item);
			
			$search_for = array('\'', '\'', '"', '<', '>');
			$replace_with = array('&apos;', '&#39;', '&quot;', '&lt;', '&gt;');

			return str_replace($search_for, $replace_with, $my_item);
		}
		
		// ================================================================== //
		
		function StartFilters()
		{
			foreach ($this->config->filters as $filter)
			{
				$filter_class_name = $filter . "Filter";
				$filter = new $filter_class_name();
				$filter->Execute();
			}
		}
		
		function StopFilters()
		{
			foreach ($this->config->filters as $filter)
			{
				$filter_class_name = $filter . "Filter";
				$filter = new $filter_class_name();
				$filter->Terminate();
			}
		}
		
		// ================================================================== //		
	}
	
	// ====================================================================== //
?>