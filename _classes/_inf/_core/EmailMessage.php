<?
	// ====================================================================== //

	class EmailMessage
	{
		// ============================================================================= //

		var $id;
		var $to;
		var $from;
		var $subject;
		var $message;
		var $plaintext;

		// ============================================================================= //

		/**
		 * This class provides all the functions needed for sending an email in php
		 * using the mail() function.
		 *
		 * @param Integer $id
		 * @param String $from
		 * @param String $to
		 * @param String $subject
		 * @param String $message
		 * @param Boolean $plaintext
		 */
		function EmailMessage($id='', $from='', $to='', $subject='', $message='', $plaintext='')
		{
			$this->id = $id;
			$this->from = $from;
			$this->to = $to;
			$this->subject = $subject;
			$this->message = $message;
			$this->plaintext = $plaintext;
		}
				
		// ============================================================================= //
		
		/**
		 * Returns appropriate headers for the email based on whether plaintext is set
		 * or not.
		 *
		 * @return String
		 */
		function GetHeaders()
		{
			if ($this->plaintext)
			{
				$headers = 'From: ' . $this->from->GetName() . ' <' . $this->from->GetEmail() . '>' . "\n" .
				'Reply-To: ' . $this->from->GetEmail() . "\n" .
				'Return-Path: ' . $this->from->GetName() . ' <' . $this->from->GetEmail() . '>' . "\n" .
				'X-Mailer: PHP/' . phpversion() . "";
			}
			else
			{
				$headers = 'From: ' . $this->from->GetName() . ' <' . $this->from->GetEmail() . '>' . "\n" .
				'Reply-To: ' . $this->from->GetEmail() . "\n" .
				'Return-Path: ' . $this->from->GetName() . ' <' . $this->from->GetEmail() . '>' . "\n" .
				'X-Mailer: PHP/' . phpversion() . "\n" .
				'Content-Type: text/html; charset=utf-8';
			}
			return $headers;
		}

		/**
		 * Gets the message variable, but also does some simple substitution to
		 * improve compatibility.
		 *
		 * @return String
		 */
		function GetMessage()
		{
			// do parsing and rendering here innit lolz
			$originals = array('\'', '"', '<', '>');
			$substitutes = array('&apos;', '&quot;', '&lt;', '&gt;');

			$this->message = str_replace($substitutes, $originals, $this->message);
			
			if ($this->plaintext == 1)
			{
				$this->message = str_replace("<br>", "\n", $this->message);
				$this->message = strip_tags($this->message);
			}
			else
			{
				$this->message = str_replace("<br>", "<br>\n", $this->message);
			}
			
			return $this->message;
		}
		
		/**
		 * Gets the subject variable, but also does some simple substitution to
		 * improve compatibility.
		 * 
		 * @return String
		 */
		function GetSubject()
		{
			// do parsing and rendering here innit lolz
			$originals = array('\'', '"');
			$substitutes = array('&apos;', '&quot;');

			$this->subject = str_replace($substitutes, $originals, $this->subject);
			return $this->subject;
		}
		
		// ============================================================================= //
		
		/**
		 * Set the from value
		 *
		 * @param String $from
		 */
		function SetFrom($from='') { $this->from = $from; }
		/**
		 * Set the to value
		 *
		 * @param String $to
		 */
		function SetTo($to='') { $this->to = $to; }
		/**
		 * Set the subject value
		 *
		 * @param String $subject
		 */
		function SetSubject($subject='') { $this->subject = $subject; }
		/**
		 * Set the message value
		 *
		 * @param String $message
		 */
		function SetMessage($message='') { $this->message = $message; }
		/**
		 * Set the plaintext value
		 *
		 * @param Boolean $plaintext
		 */
		function SetPlainText($plaintext='') { $this->plaintext = $plaintext; }

		// ============================================================================= //
		
		/**
		 * Sends the email.
		 */
		function Send()
		{
			if (empty($this->subject)) $this->subject = "(no subject)";
			if (!is_a($this->to, 'Subscriber')) return false;
			if (empty($this->message)) return false;
	
			$config = Config::GetInstance();
			
			if ($config->mail['enabled'] == true)
			{
				mail($this->to->GetEmail(), $this->GetSubject(), $this->GetMessage(), $this->GetHeaders());
			}
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>