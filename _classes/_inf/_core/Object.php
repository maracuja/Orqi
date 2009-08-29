<?
	// ================================================================================= //
	
	class Object
	{
		// ============================================================================= //
		
		public $id;
		
		var $table_name;
		var $mapper;
		var $validator;
		var $filtercolumn_name; 
		
		public $session;
		
		// ============================================================================= //
		
		/**
		 * Gets the ID
		 *
		 * @return Integer
		 */
		function GetId() { return $this->id; }
		function isNew() { return empty($this->id); }
		
		// ============================================================================= //
		
		/**
		 * Sets the ID
		 *
		 * @param Integer $id
		 */
		function SetId($id='') { $this->id = $id; }
		
		// ============================================================================= //
		
		/**
		 * Runs a check to see if all the object variables are empty except ID. It does
		 * this by matching Getter and Setter methods in the sub-class. This is part of
		 * the Lazy Loading pattern and allows us to query the data through the domain
		 * on an ad hoc basis.
		 * 
		 * If you pass TRUE in the $debug parameter then isGhost() will print out what it
		 * is doing so you can spot any errors. 
		 *
		 * @param Boolean $debug
		 * @return Boolean
		 */
		function isGhost($debug='')
		{
			$variables = get_class_vars(get_class($this));
			if (!empty($debug)) echo "variables: " . print_r($variables, 1) . "<br />";
			$boolean_var = !empty($this->id);
			if (!empty($debug))  echo "checking id: " .  $boolean_var . "<br />";
			foreach ($variables as $key => $val)
			{
				$excluded_names = array('id', 'database_tablename', 'mapper', 'filtercolumn_name', 'table_name');
				if (!in_array($key, $excluded_names) && !is_array($this->{$key}))
				{
					$boolean_var = $boolean_var && empty($this->{$key});
					if (!empty($debug)) echo "checking \$this->" . $key . "::" . $this->{$key} . "::" . $boolean_var . "<br />";
				}
			}
			return $boolean_var;
		}
		
		// ============================================================================= //
		
		/**
		 * Runs a check to see if all the object variables are empty except ID. It does
		 * this by matching Getter and Setter methods in the sub-class. This is part of
		 * the Lazy Loading pattern and allows us to query the data through the domain
		 * on an ad hoc basis.
		 * 
		 * If you pass TRUE in the $debug parameter then isGhost() will print out what it
		 * is doing so you can spot any errors. 
		 *
		 * @param Boolean $debug
		 * @return Boolean
		 */
		function isEmpty($debug='')
		{
			// test
			$variables = get_class_vars(get_class($this));
			if (!empty($debug)) echo "variables: " . print_r($variables, 1) . "<br />";
			$boolean_var = true;
			if (!empty($debug))  echo "checking id: " .  $boolean_var . "<br />";
			foreach ($variables as $key => $val)
			{
				$excluded_names = array('database_tablename', 'mapper', 'filtercolumn_name', 'table_name');
				if (!in_array($key, $excluded_names) && !is_array($this->{$key}))
				{
					if (is_a($this->{$key}, get_class($this->{$key})))
					{
						$boolean_var = $boolean_var && ($this->{$key}->GetId() == '');
						if (!empty($debug)) echo "checking \$this->" . $key . "::" . $this->{$key}->GetId() . "::" . $boolean_var . "<br />";
					}
					else
					{
						$boolean_var = $boolean_var && empty($this->{$key});
						if (!empty($debug)) echo "checking \$this->" . $key . "::" . $this->{$key} . "::" . $boolean_var . "<br />";
					}
				}
			}
			return $boolean_var;
		}
		
		// ============================================================================= //
		
		/**
		 * Enter description here...
		 *
		 * @return unknown
		 */
		function isValid()
		{
			$this->GetValidator()->Populate($this);
			return $this->GetValidator()->isValid();
		}
		
		// ============================================================================= //
		
		/**
		 * Like isGhost this will populate the object fields based on matched Getters and
		 * Setters. Although I'm not sure it needs to be that clever, a * select on a
		 * particular table and then assigning the results to the class will have the
		 * same result.
		 *
		 * If you pass TRUE in the $debug parameter then PopulateMe() will print out what it
		 * is doing so you can spot any errors. 
		 *
		 * @param Boolean $debug
		 */
		function PopulateMe($debug='')
		{
			if (!empty($debug)) echo get_class($this) . "->PopulateMe(): Start.<br />";
			$data = new Mapper();
			$data = $data->Find($this, $debug);
			if (!empty($debug)) echo get_class($this) . "->PopulateMe(): Class found:" . print_r($data, 1) . "<br />";

			$methods = get_class_methods($this);
			if (!empty($debug)) echo get_class($this) . "->PopulateMe(): Class methods: " . print_r($methods, 1) . "<br />";
			foreach ($methods as $method)
			{
				if (substr($method, 0, 3) == "Get")
				{
					$setter_method = str_replace("Get", "Set", $method);
					if (method_exists($this, $setter_method) && $method != "GetId")
					{
						if (!empty($debug)) if (!empty($debug)) echo get_class($this) . "->PopulateMe(): Populating object: \$this->" . $setter_method . "(\$data->" . $method . "()); <br />";
						$this->$setter_method($data->$method());
					}
				}
				if (substr($method, 0, 3) == "get")
				{
					$setter_method = str_replace("get", "set", $method);
					if (method_exists($this, $setter_method) && $method != "getid")
					{
						if (!empty($debug)) if (!empty($debug)) echo get_class($this) . "->PopulateMe(): Populating object: \$this->" . $setter_method . "(\$data->" . $method . "()); <br />";
						$this->$setter_method($data->$method());
					}
				}
			}
			if (!empty($debug)) echo get_class($this) . "->PopulateMe(): Finished.<br />";
		}
		
		// ============================================================================= //
		
		/**
		 * Pluralise the object name and use that value to instantiate a mapper, which is
		 * then returned. There is a caveat that we check that the object we are extending
		 * is Object. The reason for this is if we are extending another class then,
		 * likelihood is we are using that objects table to save our data in.
		 *
		 * @return Mapper
		 */
		function GetMapper()
		{
			if (empty($this->mapper))
			{
				$object_name = $this->GetPlural(get_class($this));
				$object_name = ucwords($object_name);
				
				$this->mapper = new $object_name();
			}
			return $this->mapper;
		}
		
		// ============================================================================= //

		/**
		 * Returns the mapper name.
		 *
		 * @return Mapper
		 */
		function GetMapperName()
		{
			$this->GetMapper();
			return ucwords(get_class($this->mapper));
		}
		
		// ============================================================================= //
		
		/**
		 * Return the corresponding validator object
		 *
		 * @return Validator
		 */
		function GetValidator()
		{
			if (empty($this->validator))
			{
				if (strcasecmp(get_parent_class($this), "Object") == 0) $object_name = get_class($this);
				else $object_name = get_parent_class($this);
				$object_name = ucwords($object_name) . "Validator";
				
				$this->validator = new $object_name();
			}
			return $this->validator;
		}
		
		// ============================================================================= //
		
		/**
		 * Find the object name and pluralise it to get the table name. There is a caveat that
		 * we check that the object we are extending is Object. The reason for this is if we
		 * are extending another class then, likelihood is we are using that objects table
		 * to save our data in.
		 * 
		 * Example:
		 * 
		 * A Resource has an id, a name and a url
		 * A Link extends Resource.
		 * 
		 * We would probably want to save Links to the resources table and use the Resources
		 * mapper, etc.
		 *
		 * @return String.
		 */
		function GetTableName()
		{
			if (empty($this->table_name)) $this->table_name = strtolower($this->GetPlural(get_class($this)));
			return $this->table_name;
		}
		
		// ============================================================================= //
		
		/**
		 * Creates a foreign key database field name of the object. eg Product > products_id
		 *
		 * @return Boolean
		 */
		function GetFilterColumnName()
		{
			$this->filtercolumn_name = strtolower(get_class($this)); 
			return $this->filtercolumn_name;
		}
		
		// ============================================================================= //
		
		/**
		 * Pluralises the word in $string. If field is set to TRUE then return the value
		 * as a field name.
		 *
		 * @param String $string
		 * @param String $field
		 * @return String
		 */
		function GetPlural($string='', $field=false)
		{
			$string = strtolower(trim($string));
			$field = (empty($field)) ? "" : "_id";
			
			switch (true)
			{
				case (substr($string, -2) == 'es'): $string = $string; break;
				case (substr($string, -1) == 's'): $string = $string . "es"; break;
				case (substr($string, -1) == 'x'): $string = $string . "es"; break;
				case (substr($string, -1) == 'y'): $string = substr($string, 0, -1) . "ies"; break;
				default:  $string = $string . "s"; break;
			}
			
			return $string . $field;
		}
		
		// ============================================================================= //
		
		/**
		 * Enter description here...
		 *
		 * @return Bool
		 */
		function Save()
		{
			return $this->GetMapper()->Save($this);
		}
		
		// ============================================================================= //
		
		/**
		 * 
		 */
		function Delete()
		{
			return $this->GetMapper()->Delete($this);
		}
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>