<?
	// ================================================================================= //		
	
	class Validator
	{
		// ============================================================================= //
		
		var $id;
		
		var $object;
		var $action;
		
		var $errors = array();
		
		var $post;
		var $files;
		var $config;
		
		// ============================================================================= //
		
		
		function __construct($post='', $files='')
		{
			$this->config = Config::GetInstance();
			$this->files = $files;
			$this->post = $post;
			$this->errors = array();
			
			$this->Populate($this->post, $this->files);
		}
		
		// ============================================================================= //
		
		/**
		 * Gets the ID
		 *
		 * @return Integer
		 */
		function GetId() { return $this->id; }
		/**
		 * Sets the ID
		 *
		 * @param Integer $id
		 */
		function SetId($id='') { $this->id = $id; }
		
		// ============================================================================= //
		
		/**
		 * Get the object
		 *
		 * @return String
		 */
		function GetObject() { return $this->object; }
		/**
		 * Get the action
		 *
		 * @return String
		 */
		function GetAction() { return $this->action; }
		/**
		 * Get the errors
		 *
		 * @return Message[]
		 */
		function GetErrors() { return $this->errors; }
		
		// ============================================================================= //
		
		/**
		 * Set the object
		 *
		 * @param String $object
		 */
		function SetObject($object='') { $this->object = $object; }
		/**
		 * Set the action
		 *
		 * @param String $action
		 */
		function SetAction($action='') { $this->action = $action; }
		
		// ============================================================================= //
		
		/**
		 * Add an error message to the array
		 *
		 * @param Message $error_message
		 */
		function AddErrorMessage($error_message='')
		{
			if (is_a($error_message, 'Message')) $this->errors[] = $error_message;
		}
		
		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid Integer
		 *
		 * @param String $value
		 * @return Boolean
		 */
		function IsValidAlphanumeric($value) { return ereg('^([-\._a-zA-Z0-9 ])+$', $value); }
		
			/**
		 * Checks that the $value is a valid Integer
		 *
		 * @param String $value
		 * @return Boolean
		 */
		function IsValidSlug($value) { return ereg('^([-_a-zA-Z0-9])+$', $value); }
		
		/**
		 * Checks that the $value is a valid Integer
		 *
		 * @param Integer $value
		 * @return Boolean
		 */
		function IsValidInt($value) { return ereg('^([0-9])+$', $value); }
		function IsValidDecimal($value) { return ereg('^([0-9\.])+$', $value); }
	
		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid Boolean
		 *
		 * @param Boolean $value
		 * @return Boolean
		 */
		function IsValidBool($value) { return ereg('^[0-1]$', $value); }

		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid Email Address
		 *
		 * @param String $value
		 * @return Boolean
		 */
		function IsValidEmail($value) { return ereg("^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$", $value); }
		
		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid Date field
		 *
		 * @param Date $value
		 * @return Boolean
		 */
		function IsValidDate($value) { return $this->IsValidDDMMYYYY($value) || $this->IsValidYYYYMMDD($value); }

		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid URL
		 *
		 * @param URL $value
		 * @return Boolean
		 */
		function IsValidURL($value) { return eregi("^(http://(www\.|([a-z0-9_-]+)\.)?)([a-z0-9_-]+)(\.[a-z]{2,4}){1,2}([/[a-z0-9\._-]*)+((\?([a-z0-9\._-]+)=([a-z0-9\._-]*)){1}(\&([a-z0-9\._-]+)=([a-z0-9\._-]*))*)?$", $value); }
		
		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid hexidecimal colour value
		 *
		 * @param String $value
		 * @return Boolean
		 */
		function IsValidColour($value) { return ereg('^([0-9A-Fa-f]{6})+$', $value); }
		
		// ============================================================================= //
		
		/**
		 * Checks that the $value is a valid DD/MM/YYYY style date.
		 *
		 * @param String $value
		 * @return Boolean
		 */
		function IsValidDDMMYYYY($value) { return ereg('^([0-9]{1,2})[-/]([0-9]{1,2})[-/]([0-9]{4})+$', $value); }
		
		// ============================================================================= //

		/**
		 * Checks that the $value is a valid YYYY/MM/DD style date.
		 *
		 * @param String $value
		 * @return Boolean
		 */
		function IsValidYYYYMMDD($value) { return ereg('^([0-9]{4})[-/]([0-9]{1,2})[-/]([0-9]{1,2})+$', $value); }
		
		// ============================================================================= //
		
		/**
		 * IsValidFile checks the files upload object and matches against any file types that
		 * are passed in. Careful that 
		 *
		 * @param Array() $value
		 * @return String | true
		 */
		function ValidateFile($value, $file_types=array())
		{
			switch ($value['error'])
			{
				case UPLOAD_ERR_INI_SIZE:	return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
				case UPLOAD_ERR_FORM_SIZE:	return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
				case UPLOAD_ERR_PARTIAL:	return "The uploaded file was only partially uploaded.";
				case UPLOAD_ERR_NO_FILE:	return "No file was uploaded.";
				case UPLOAD_ERR_NO_TMP_DIR:	return "A server error is preventing file uploads. Please contact the site administrator.";
				case UPLOAD_ERR_CANT_WRITE:	return "Failed to write file to disk.";
				case UPLOAD_ERR_EXTENSION:	return "File upload stopped by extension.";
			}
			if (in_array($value['type'], $file_types)) return true;
			else return "File type is not correct.";
		}
		
		// ============================================================================= //
		
		/**
		 * Takes in an error name, looks for it in the errors array and if it's there,
		 * wraps some HTML around it and returns. 
		 *
		 * @param String $error_name
		 * @param String $tag Name of a tag to wrap the message in
		 * @param String $attr An array of attibutes
		 * @return String
		 */
		function PrintError($error_name='', $tag='', $attr='')
		{
			if (!is_a($this->config, 'Config')) $this->config = Config::GetInstance();
			$tag = (empty($tag)) ? $this->config->app['error_tag'] : $tag;
			$attr = (empty($attr)) ? $this->config->app['error_tag_attr'] : $attr;
			$return_string = '';
			if (!empty($error_name))
			{
				foreach ($this->errors as $error)
				{
					$error = Caster::Cast($error, new Message());
					if ($error->GetCode() == $error_name)
					{
						if (!empty($tag)) $return_string .= "<$tag $attr>";
						$return_string .= $error->GetText();
						if (!empty($tag)) $return_string .= "</$tag>";
					}
				}
			}
			return $return_string;
		}
		
		// ============================================================================= //
		
		/**
		 * Outputs all errors discovered by the Validator
		 *
		 * @return String
		 */
		function PrintErrors()
		{
			$return_string = '';
			if (!empty($this->errors))
			{
				$return_string .= "
				    <fieldset class='error'>
					    <div id='field'>
				";
				
				$return_string .= "<ul>";
				foreach ($this->errors as $error)
				{
					$error = Caster::Cast($error, new Message());
					$return_string .= "<li>" . $error->GetText() . "</li>";
				}
				$return_string .= "</ul>";
								
				$return_string .= "
						</div>
					</fieldset>
				";
			}
			return $return_string;
		}
		
		// ============================================================================= //
		
		function ErrorsToJson()
		{
			$json_string = '{"errors": [';
			$separator = '';
			foreach ($this->errors as $error)
			{
				$json_string .= $separator . '{ "name": "' . $error->GetCode() . '", "text" : "' . $error->GetText() . '" }';
				$separator = ', ';	
			}
			$json_string .= " ] }";
			return $json_string;
		}
		
		// ============================================================================= //
		
		function isValid()
		{
			$this->errors = $this->TestConditions();
			return empty($this->errors);
		}
		
		function TestConditions()
		{
			$errors = array();
			return $errors;
		}
		
		function Populate($post='', $files='') { }
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>