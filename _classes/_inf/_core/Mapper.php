<?
	// ================================================================================= //
	
	class Mapper
	{
		// ============================================================================= //
		
		var $config;
		var $session;
		var $dao;
		
		// ============================================================================= //

		function __construct()
		{
			$this->config = Config::GetInstance();
			$this->dao = new DAO();
			$this->session = new Session();
		}
		
		// ============================================================================= //

		/**
		 * Delete takes in an object, assumes the object name is the table name and
		 * deletes from that table based on the object $id value.
		 * 
		 * If you have a an object linked to a table name that is an irregular plural
		 * then you can set a variable in your object called $database_tablename to
		 * the actual table name and Delete will use that instead.
		 * 
		 * Example usage: -
		 * 
		 * $mapper = new Mapper();
		 * $blogentry = new Blogentry(1);
		 * 
		 * $mapper->Delete($blogentry);
		 *
		 * @param Object $object
		 * @return boolean
		 */
		function Delete($object='')
		{
			if (gettype($object) != 'object') return false;
			$sql_string = "delete from " . $object->GetTableName() . " where id=" . $object->GetId();
			return $this->dao->ExecuteQuery($sql_string);
		}

		// ============================================================================= //

		/**
		 * Find takes in an object, assumes the object name is the table name and
		 * searches from that table based on the object $id value.
		 * 
		 * 
		 * If you have a an object linked to a table name that is an irregular plural
		 * then you can set a variable in your object called $database_tablename to
		 * the actual table name and Delete will use that instead.
		 * 
		 * You don't really have to use find because Orqi implements lazy loading. You
		 * can simply instantiate a domain object and Orqi will call Find when you call
		 * one of the object's getters.
		 * 
		 * Example usage: -
		 * 
		 * $blogentry = new Blogentry(1);
		 * echo $blogentry->GetTitle();		// Find is called at this point.
		 * 
		 * --- or ---
		 * 
		 * $mapper = new Mapper();
		 * $blogentry = $mapper->Find(new Blogentry(1));
		 *
		 * @param Object $object
		 * @param boolean $debug
		 * @return boolean
		 */
		function Find($object='', $debug='')
		{
			if (gettype($object) != 'object') return false;
			// set the sql. because we're using Lazy Loading, we only need the
			// object's table and then we cast the record into an object.
			$sql_string = "select * from " . $object->GetTableName() . " where id=" . $object->GetId();
			if (!empty($debug)) echo $sql_string;
			$recordset = $this->dao->ExecuteQuery($sql_string);
			// return the row as an object if found
			if (mysql_num_rows($recordset) > 0) return Caster::Cast(mysql_fetch_assoc($recordset), $object);
			else return false;
		}
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $id
		 * @return unknown
		 */
		function FindById($id='')
		{
			$validator = new Validator();
			if ($validator->IsValidInt($id)) $sql_string = "select * from `" . strtolower(get_class($this)) . "` where id=" . $id;
			else if (!empty($id)) $sql_string = "select * from `" . strtolower(get_class($this)) . "` where slug='" . $id . "'";
			else return false;
			
			$model_object_name = $this->Depluralize(get_class($this));
			return $this->dao->GetObject($sql_string, new $model_object_name());
		}
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $string
		 * @return unknown
		 */
		function Depluralize($string='')
		{
			$string = strtolower(trim($string));
			switch (true)
			{
				case (substr($string, -4) == 'sses'):	$string = substr($string, 0, -2);		break;
				case (substr($string, -3) == 'ies'):	$string = substr($string, 0, -3) . "y";	break;
				case (substr($string, -2) == 'es'):		$string = substr($string, 0, -1);		break;
				default:								$string = substr($string, 0, -1);		break;
			}
			return $string;
		}
		
		// ============================================================================= //

		/**
		 * FindBy does XYZ
		 *
		 * @param Object $findme
		 * @param Object $by
		 * @param int $page
		 * @return Collection
		 */
		function FindBy($findme='', $by='', $page='', $debug='')
		{
			$data = array();
			if (gettype($findme) != 'object' || gettype($by) != 'object') return false;
			
			$sql_string = "
				select	*
				from	" . $findme->GetTableName() . "
				where	" . $by->GetFilterColumnName() . "=" . $by->GetId() . "
			";
			
			if (!empty($page))
			{
				$cursor = ($page-1) * $this->config->page['items'];
				$sql_string .= "
					limit		" . $cursor . ", " . $this->config->page['items'] . "
				";
			}

			if (!empty($debug)) echo $sql_string;
			
			$recordset = $this->dao->ExecuteQuery($sql_string);
			if (mysql_num_rows($recordset) > 0) while ($record = mysql_fetch_assoc($recordset)) $data[] = Caster::Cast($record, $findme);
			return $data;
		}
		
		// ============================================================================= //

		/**
		 * Find takes in an object, assumes the object name is the table name and
		 * searches from that table based on the object $id value.
		 * 
		 * If you have a an object linked to a table name that is an irregular plural
		 * then you can set a variable in your object called $database_tablename to
		 * the actual table name and Delete will use that instead.
		 * 
		 * You don't really have to use find because Orqi implements lazy loading. You
		 * can simply instantiate a domain object and Orqi will call Find when you call
		 * one of the object's getters.
		 * 
		 * Example usage: -
		 * 
		 * $blogentry = new Blogentry(1);
		 * echo $blogentry->GetTitle();		// Find is called at this point.
		 * 
		 * --- or ---
		 * 
		 * $mapper = new Mapper();
		 * $blogentry = $mapper->Find(new Blogentry(1));
		 *
		 * @param Object $object
		 * @param boolean $debug
		 * @return boolean
		 */
		function GetTotalRows($object='', $debug='')
		{
			if (gettype($object) != 'object') return 0;
			// set the sql. because we're using Lazy Loading, we only need the
			// object's table and then we cast the record into an object.
			$sql_string = "select count(*) as total_rows from " . $object->GetTableName();
			if (!empty($debug)) echo $sql_string;
			$recordset = $this->dao->ExecuteQuery($sql_string);
			// return the row as an object if found
			$record = mysql_fetch_assoc($recordset);
			return $record['total_rows'];
		}
		
		// ============================================================================= //

		/**
		 * FindBy does XYZ
		 *
		 * @param Object $findme
		 * @param Object $by
		 * @return Collection
		 */
		function GetTotalRowsBy($findme='', $by='', $debug='')
		{
			if (gettype($findme) != 'object' || gettype($by) != 'object') return false;
			
			$sql_string = "
				select	count(*) as total_rows
				from	" . $findme->GetTableName() . "
				where	" . $by->GetFilterColumnName() . "=" . $by->GetId() . "
			";

			if (!empty($debug)) echo $sql_string;
			
			$recordset = $this->dao->ExecuteQuery($sql_string);
			$record = mysql_fetch_assoc($recordset);
			return $record['total_rows'];
		}
		
		// ================================================================== //
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $page
		 * @return unknown
		 */
		function GetPageClause($page='')
		{
			if (!empty($page))
			{
				$cursor = ($page-1) * $this->config->page['items'];
				return " limit " . $cursor . ", " . $this->config->page['items'] . " ";
			}
			else return "";
		}
		
		function GetQueryClause($query='', $fields=array())
		{
			$query_clause = '';
			if (!empty($query) && !empty($fields))
			{
				$query = explode(" ", $query);
				$separator = "";
				
				foreach ($fields as $field)
				{
					foreach ($query as $item)
					{
						$query_clause .= $separator . $field . " like '%" . $item . "%'";
						$separator = " or ";
					}
				}
			}
			return $query_clause;
		}
		
		// ============================================================================= //
		
		function ToString()
		{
			echo "
				Data
				<pre>" . print_r($this->data, 1) . "</pre>
				Errors
				<pre>" . print_r($this->errors, 1) . "</pre>
			";
		}
				
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>