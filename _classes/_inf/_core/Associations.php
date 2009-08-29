<?
	// ================================================================================= //
	
	class Associations
	{
		// ============================================================================= //
		
		var $config;
		var $dao;
		
		var $table_name;
		
		// ============================================================================= //

		function Associations($table_name='')
		{
			$this->config = Config::GetInstance();
			$this->dao = new DAO();
			
			$this->table_name = $table_name;
		}
		
		// ============================================================================= //
		
		function SetTablename($table_name='') { $this->table_name = strtolower($table_name); }

		function GetTablename($a='', $b='')
		{
			if (empty($this->table_name))
			{
				$this->table_name = strtolower(get_class($a));
				$this->table_name .= $b->GetTableName();
			}
			return $this->table_name;
		}
		
		// ============================================================================= //

		/**
		 * Delete will delete only records from intersection tables. The filtering
		 * is done based on how the objects are populated. If they have id's then
		 * the constraint clauses are written into the sql.
		 *
		 * To delete all the products in a particular section ...
		 * 
		 * $product = new Product();
		 * $section = new Section(1);
		 * 
		 * $association = new Association();
		 * $association->Delete($product, $section); 
		 *
		 * @param Object $a
		 * @param Object $b
		 * @return boolean
		 */
		function Delete($a='', $b='')
		{
			if (gettype($a) != 'object' || gettype($b) != 'object') return false;
		
			switch (true)
			{
				case ($a->GetId() && $b->GetId()):
					$sql_string = "
						delete	from " . $this->GetTablename($a, $b) . "
						where	" . $a->GetFilterColumnName() . "=" . $a->GetId() . "
						and		" . $b->GetFilterColumnName() . "=" . $b->GetId() . "
					";
					break;
				
				case (!$a->GetId() && $b->GetId()):
					$sql_string = "
						delete	from " . $this->GetTablename($a, $b) . "
						where	" . $b->GetFilterColumnName() . "=" . $b->GetId() . "
					";
					break;
					
				case ($a->GetId() && !$b->GetId()):
					$sql_string = "
						delete	from " . $this->GetTablename($a, $b) . "
						where	" . $a->GetFilterColumnName() . "=" . $a->GetId() . "
					";
					break;
					
				default:
					$sql_string = "";
					break;
			}
			
			return $this->dao->ExecuteQuery($sql_string);
		}

		// ============================================================================= //
		
		/**
		 * Save the association between two objects. If it already exists then
		 * returns nothing. If you want your association to be richer, you must
		 * write the functionality in the corresponding Mapper. The following is
		 * some example code that shows a typical save ...
		 *
		 * $mapper = new Mapper();
		 * $association = new Association();
		 * 
		 * $product = $mapper->Find(new Product(1));
		 * $section = $mapper->Find(new Section(1));
		 * 
		 * $association->Save($product, $section); 
		 * 
		 * @param Object $a
		 * @param Object $b
		 * @return boolean
		 */
		function Save($a='', $b='')
		{
			if (gettype($a) != 'object' || gettype($b) != 'object') return false;
			
			if (!$this->Find($a, $b, 1))
			{
				$sql_string = "
					insert into		" . $this->GetTablename($a, $b) . "
					set				" . $a->GetFilterColumnName() . "=" . $a->GetId() . ",
									" . $b->GetFilterColumnName() . "=" . $b->GetId() . "
				";

				return $this->dao->ExecuteQuery($sql_string);
			}
			else return false;
		}
		
		// ============================================================================= //

		/**
		 * Find will search for object via intersection tables. For instance if you
		 * want to find all the products in a section you call the function like this ...
		 * 
		 * $product = new Product();
		 * $section = new Section(1);
		 * 
		 * $association = new Associations();
		 * $association->Find($product, $section);
		 * 
		 * If you want to search on a simple 1>M relationship, say, find products with one
		 * kind of product type use the Mapper object.
		 * 
		 * @param Object $a
		 * @param Object $b
		 * @param int $page
		 * @return Collection
		 */
		function Find($a='', $b='', $page='', $debug='')
		{
			$data = array();
			if (gettype($a) != 'object' || gettype($b) != 'object') return false;
			
			switch (true)
			{
				case ($a->GetId() && $b->GetId()):
					$sql_string = "
						select	" . $this->GetTablename($a, $b) . ".*
						from	" . $this->GetTablename($a, $b) . "
						where	" . $a->GetFilterColumnName() . "=" . $a->GetId() . "
						and		" . $b->GetFilterColumnName() . "=" . $b->GetId() . "
					";
					$return_object = new Association();
					break;
				
				case (!$a->GetId() && $b->GetId()):
					$sql_string = "
						select	" . $a->GetTableName() . ".*
						from	" . $this->GetTablename($a, $b) . ", " . $a->GetTableName() . "
						where	" . $b->GetFilterColumnName() . "=" . $b->GetId() . "
						and		" . $a->GetFilterColumnName() . "=" . $a->GetTableName() . ".id
					";
					
					$return_object = $a;
					break;
					
				case ($a->GetId() && !$b->GetId()):
					$sql_string = "
						select	" . $b->GetTableName() . ".*
						from	" . $this->GetTablename($a, $b) . ", " . $b->GetTableName() . "
						where	" . $a->GetFilterColumnName() . "=" . $a->GetId() . "
						and		" . $b->GetFilterColumnName() . "=" . $b->GetTableName() . ".id
					";
					
					$return_object = $b;
					break;
					
				case (!$a->GetId() && !$b->GetId()):
					$sql_string = "select * from " . $this->GetTablename($a, $b);
					$return_object = new Association();
					break;
					
				default:
					$sql_string = "";
					$return_object = "";
					break;
			}
			
			if (!empty($debug)) echo $sql_string;
			
			if (!empty($sql_string))
			{
				if (!empty($page))
				{
					$cursor = ($page-1) * $this->config->page['items'];
					$sql_string .= "
						limit		" . $cursor . ", " . $this->config->page['items'] . "
					";
				}
				$recordset = $this->dao->ExecuteQuery($sql_string);
				if (mysql_num_rows($recordset) > 0)
				{
					if (empty($return_object)) while ($record = mysql_fetch_assoc($recordset)) $data[] = $record;
					else while ($record = mysql_fetch_assoc($recordset)) $data[] = Caster::Cast($record, $return_object);
				}
			}
			
			return $data;
		}

		// ============================================================================= //
	}
	
	// ================================================================================= //
?>