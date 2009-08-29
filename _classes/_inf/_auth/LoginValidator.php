<?
	// ================================================================================= //
	
	class LoginValidator extends Validator
	{
		// ============================================================================= //

		var $username;
		var $password;
		var $redirect;
		
		var $user;
		
		// ============================================================================= //

		function LoginValidator($post='') { $this->Populate($post); }
		
		// ============================================================================= //
		
		function GetUsername() { return $this->username; }
		function GetPassword() { return $this->password; }
		function GetRedirect() { return $this->redirect; }
		function GetUser() { return $this->user; }
		
		// ============================================================================= //
		
		function SetUsername($username='') { $this->username = $username; }
		function SetPassword($password='') { $this->password = $password; }
		function SetRedirect($redirect='') { $this->redirect = $redirect; }
		
		// ============================================================================= //
		
		function Populate($post='')
		{
			$this->object = $post['object'];
			$this->action = $post['action'];
				
			$this->username = $post['username'];
			$this->password = $post['password'];
			$this->redirect = $post['redirect'];
		}
		
		// ============================================================================= //
		
		function isValid()
		{
			$this->errors = array();
			
			if (empty($this->username)) $this->errors[] = new Message('username', "You must type a username.");
			if (empty($this->password)) $this->errors[] = new Message('password', "You must type a password.");
			if (!empty($this->username) && !empty($this->password))
			{
				$this->user = new Users();
				$this->user = $this->user->ValidUser($this->username, $this->password);
				if (!is_a($this->user, 'User')) $this->errors[] = new Message('invalid_user', "Couldn't find the user details.");
			}

			return empty($this->errors);
		}

		// ============================================================================= //
	}
	
	// ================================================================================= //
?>