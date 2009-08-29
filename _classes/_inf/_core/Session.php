<?
	// ====================================================================== //
	
	class Session
	{
		// ================================================================== //
		
		var $config;
		var $user;
		var $languages;
		var $labels;
		var $cookie;
		var $phpsessid;
		var $messages = array();
		
		// ================================================================== //

		/**
		 * Session object maintains an instance of the current User, the User's
		 * Usergroup and the User's Language.
		 */
		function Session()
		{
			$this->config = Config::GetInstance();
			$this->phpsessid = $_REQUEST['PHPSESSID'];
			if (!empty($_SESSION[$this->config->app['cookiename'] . '_messages'])) $this->messages = $_SESSION[$this->config->app['cookiename'] . '_messages'];
			
			$this->LoadItems();
			$this->LoadCookie();
			$this->LoadUser();
		}
		
		// ================================================================== //

		/**
		 * Checks the _SESSION for a User, if there is no User in the session
		 * then checks the _COOKIE. If we still don't have a valid User then
		 * we instantiate a stub Guest User with a Guest Usergroup and the default
		 * language.
		 */
		function LoadUser()
		{
			$this->user = new User();
			
			// check the session first
			if (!empty($_SESSION[$this->config->app['cookiename'] . '_user']))
			{
				$this->user = Caster::Cast($_SESSION[$this->config->app['cookiename'] . '_user'], new User());
			}
			// then check the cookie
			else
			{
				$this->user = new LoginValidator();
				$this->user->SetUsername($this->cookie[$this->config->app['cookiename'] . "_USERNAME"]);
				$this->user->SetPassword($this->cookie[$this->config->app['cookiename'] . "_PASSWORD"]);
				if ($this->user->isValid()) $this->SetUser($this->user->GetUser());
			}

			// otherwise just set a blank user.
			if (!is_a($this->user, 'User')) $this->ResetUser();
		}
		
		// ================================================================== //

		/**
		 * Load the _COOKIE object into the session.
		 */
		function LoadCookie()
		{
			$this->cookie = array();
			foreach ($_COOKIE as $key => $val) $this->cookie[$key] = $val;
		}
		
		// ================================================================== //

		/**
		 * Load the temporary variables.
		 */
		function LoadItems()
		{
			if (!empty($_SESSION[$this->config->app['cookiename'] . '_temp']))
			{
				foreach ($_SESSION[$this->config->app['cookiename'] . '_temp'] as $key => $val) $this->$key = $val;
			}
		}
		
		// ================================================================== //

		/**
		 * Takes in a User and sets them as the Current User. The History code is
		 * for tracking User behaviour in projects with the History class included.
		 * 
		 * The new User's labels are loaded as it's likely that they have a different
		 * language set as their default.
		 *
		 * @param User $user
		 */
		function SetUser($user='', $remember='')
		{
			if (!is_a($user, 'User')) $user = new User();
			
			if (!empty($remember) || !empty($_COOKIE[$this->config->app['cookiename'] . '_REMEMBER']))
			{
				setcookie($this->config->app['cookiename'] . '_USERNAME', $user->GetUsername(), time()+2592000, '/');
				setcookie($this->config->app['cookiename'] . '_PASSWORD', $user->GetPassword(), time()+2592000, '/');
				setcookie($this->config->app['cookiename'] . '_REMEMBER', 1, time()+2592000, '/');
			}
			else
			{
				setcookie($this->config->app['cookiename'] . '_USERNAME', '', time()-3600, '/');
				setcookie($this->config->app['cookiename'] . '_PASSWORD', '', time()-3600, '/');
				setcookie($this->config->app['cookiename'] . '_REMEMBER', '', time()-3600, '/');
			}
			$_SESSION[$this->config->app['cookiename'] . '_user'] = $user;

			
			$this->LoadUser();
		}
		
		// ================================================================== //

		/**
		 * Create a stub Guest User and set them as the default. Give them a
		 * Language and set their rights to the Guest Usergroup.
		 */
		function ResetUser()
		{
			$this->user = new User();
			$usergroup = new Usergroups();

			setcookie($this->config->app['cookiename'] . '_USERNAME', '', time()-3600, '/');
			setcookie($this->config->app['cookiename'] . '_PASSWORD', '', time()-3600, '/');
			setcookie($this->config->app['cookiename'] . '_REMEMBER', '', time()-3600, '/');
		
			$this->user->SetUsergroup($usergroup->FindByLevel(1)); // Guest
			$_SESSION[$this->config->app['cookiename'] . '_user'] = $this->user;
		}
		
		// ================================================================== //

		/**
		 * Add an item to the temporary array
		 *
		 * @param String $name
		 * @param String $value
		 */
		function AddItem($name, $value)
		{
			$_SESSION[$this->config->app['cookiename'] . '_temp'][$name] = $value;
			$this->$name = $value;
		}
		
		function RemoveItem($name)
		{
			$_SESSION[$this->config->app['cookiename'] . '_temp'][$name] = '';
			$this->$name = '';
		}
		
		function GetItem($name)
		{
			$item = $_SESSION[$this->config->app['cookiename'] . '_temp'][$name];
			$this->RemoveItem($name);
			return $item;
		}
		
		// ================================================================== //

		/**
		 * Add a Message object to the Messages array
		 *
		 * @param Message $message
		 */
		function AddMessage($message='')
		{
			$_SESSION[$this->config->app['cookiename'] . '_messages'][] = $message;
			$this->messages = $_SESSION[$this->config->app['cookiename'] . '_messages'];
		}
		
		// ================================================================== //
		
		function HasMessages()
		{
			if (empty($_SESSION[$this->config->app['cookiename'] . '_messages'])) return false;
			else return true;
		}
		
		/**
		 * Don't include the delimiters in the tag name, just 'li' or 'p' is fine
		 *
		 * @param String $tag_name
		 * @param String $css
		 * @return String
		 */
		function PrintMessages($list_tag='', $list_css='', $item_tag='', $item_css='')
		{
			if (!empty($_SESSION[$this->config->app['cookiename'] . '_messages']))
			{
				$return_html = (!empty($list_tag)) ? "<$list_tag class='$list_css'>" : '';
			}
			foreach ($_SESSION[$this->config->app['cookiename'] . '_messages'] as $message)
			{
				$return_html .= (!empty($item_tag)) ? "<$item_tag class='$item_css'>" : '';
				if (is_a($message, 'Message')) $return_html .= $message->GetText();
				else
				{
					$message = (array) $message;
					$return_html .= $message['text'];
				}
				$return_html .= (!empty($item_tag)) ? "</$item_tag>" : '';
			}
			if (!empty($_SESSION[$this->config->app['cookiename'] . '_messages']))
			{
				$return_html .= (!empty($list_tag)) ? "</$list_tag>" : '';
			}
			
			session_unregister($this->config->app['cookiename'] . '_messages');
			return $return_html;
		}
		
		// ================================================================== //
		
		function hasLoggedInUser()
		{
			return ($this->user->GetUsername() != "") ? true : false;
		}
		
		// ================================================================== //
		
		/**
		 * Print the object out to HTML
		 */
		function ToString()
		{
			echo "
				<h3>Session Object</h3>
				
				<p>User Details</p>
				<pre>" . print_r($this->user, 1) . "</pre>
				
				<p>Language Details</p>
				<pre>" . print_r($this->languages, 1) . "</pre>
				
				<p>Labels Details</p>
				<pre>" . print_r($this->labels, 1) . "</pre>
				
				<p>Cookie Details</p>
				<pre>" . print_r($this->cookie, 1) . "</pre>
			";
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>