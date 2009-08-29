<?
	// ================================================================================= //
	
	class Subscriber
	{
		// ============================================================================= //
		
		var $name;
		var $email;
		
		// ============================================================================= //
		
		function Subscriber($id='', $name='', $email='')
		{
			$this->id = $id;
			$this->name = $name;
			$this->email = $email;
		}
		
		// ============================================================================= //
		
		/**
		 * Get the Name
		 *
		 * @return String
		 */
		function GetName()
		{
			return $this->name;
		}
		/**
		 * Get the Email
		 *
		 * @return String
		 */
		function GetEmail()
		{
			return $this->email;
		}
		
		// ============================================================================= //
		
		/**
		 * Set the Name
		 *
		 * @param String $name
		 */
		function SetName($name='') { $this->name = $name; }
		/**
		 * Set the Email
		 *
		 * @param String $email
		 */
		function SetEmail($email='') { $this->email = $email; }
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>