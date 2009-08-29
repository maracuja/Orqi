<?php
	class SecurityFilter
	{
		public $config;
		public $session;
		
		function SecurityFilter()
		{
			$this->config = Config::GetInstance();
			$this->session = new Session();
		}
		
		function Execute()
		{
			// check the access
			$task = new Tasks();
			$task = $task->FindByFields(Request::GetGet('object'), Request::GetGet('action'));

			if ($task)
			{
				// these temp variables are being setup because of STUPID PHP4 aaargh
				// other wise we could do method chaining like blah()->blah()->blah();
				$task_ug = $task->GetUsergroup();
				$session_ug = $this->session->user->GetUsergroup();

				// if the redirect isn't working and you're getting fatal errors on the
				// supposed secure pages, you need to setup that pages in the tasks and
				// then CheckAccess will pick it up and send through the redirect.
				if ($session_ug->GetLevel() < $task_ug->GetLevel())
				{
					Request::Redirect(URLHelpers::MakeLink('user', 'login', 'redirect=' . base64_encode($this->config->page['current'])));
				}
			}
		}
		
		function Terminate() {}
	}
?>