<?
	// ================================================================================= //
	
	/**
	 * An association is basically two or more objects linked in an intersection table.
	 * When we ask for a list of stuff from the Associations mapper, we should return
	 * lists with elements of type Association.
	 *
	 */
	class Association
	{
		// ============================================================================= //
		
		function Association() {}
		
		// ============================================================================= //
		
		function GetId() { return $this->id; }
		
		// ============================================================================= //

		/**
		 * Get takes in a string which is the object type and returns an instiated ghost of
		 * that type with an id which (hopefully) is one of the member values.
		 *
		 * @param String $object
		 * @return an object of type $object
		 * 
		 */
		function Get($object='')
		{
			if (empty($object)) return false;
			else $object = new $object();
			
			$index_name = $object->GetFilterColumnName(); // $this->GetPlural(get_class($object), true);
			$object->SetId($this->$index_name);
			
			return $object;
		}

		// ============================================================================= //
	}
	
	// ================================================================================= //
?>