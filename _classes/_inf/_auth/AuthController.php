<?
	// ====================================================================== //

	class AuthController extends Controller
	{
		// ================================================================== //
		
		function Logoff() { $this->Logout(); }
		function Logout()
		{
			$this->session->ResetUser();
			$this->session->AddMessage(new Message('goodbye', "You have successfully logged out."));
			$this->Redirect($this->MakeLink('', ''));
		}
		
		function Logon() { $this->Login(); }
		function Login()
		{
			$this->form = new LoginValidator();
			
			if (empty($this->post))
			{
				$this->form->SetRedirect(base64_decode($this->get['redirect']));
			}
			else
			{
				$this->form->Populate($this->post);
				if ($this->form->isValid())
				{
					$this->session->SetUser($this->form->GetUser(), $this->post['remember']);
					$this->session->AddMessage(new Message('hello', "You have successfully logged in."));
					if (!empty($this->get['redirect'])) $this->Redirect(base64_decode($this->get['redirect']));
					else $this->Redirect($this->MakeLink('', ''));
				}
			}
			
			$this->LoadTemplate("FormLogin");
		}
		
		// ================================================================== //
		
		function Password()
		{
			if (!empty($this->post))
			{
				$user_mapper = new Users();
				$this->object = $user_mapper->FindByUsername($this->post['username']);
					
				if ($this->object instanceof User)
				{
					$email_message = new EmailMessage();
					$email_message->SetFrom(new Subscriber('', $this->config->mail['name'], $this->config->mail['address']));
					$email_message->SetTo(new Subscriber('', $this->object->GetName(), $this->object->GetEmail()));
					$email_message->SetSubject($this->config->app['title'] . ': Password Reminder');
					$email_message->SetMessage($this->LoadTemplate("EmailPasswordForgot", false));
					$email_message->SetPlaintext(1);
					$email_message->Send();
				}
				
				$this->Redirect($this->MakeLink('page', 'passwordsent'));
			}
			
			$this->LoadTemplate("FormForgotPassword");
		}
		
		// ================================================================== //		
	}
	
	// ====================================================================== //
?>