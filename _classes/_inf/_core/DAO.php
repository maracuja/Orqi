<?
	// ====================================================================== //
	
	class DAO
	{
		// ================================================================== //
		
		var $config;
		var $conn;
		
		// ================================================================== //

		/**
		 * Simple DAO object that simply gets database connections and executes
		 * SQL queries. The Config object contains the database connection details.
		 */
		function DAO()
		{
			$this->config = Config::GetInstance();
			$this->ConnectToDB();
		}
	
		// ================================================================== //

		/**
		 * This takes in a SQL string and tries to execute it. If the statement
		 * succeeded we return TRUE, otherwise FALSE.
		 *
		 * @param String $sql_string
		 * @return Boolean
		 */
		function ExecuteQuery($sql_string='')
		{
			if (empty($sql_string)) return false;
			if ($this->ConnectToDB()) return mysql_query($sql_string, $this->conn);
			else return false;
		}
		
		// ================================================================== //
		
		/**
		 * Attemps to establish a database connection
		 *
		 * @return Boolean
		 */
		function ConnectToDB()
		{
			if (empty($this->conn))
			{
				if (empty($this->config->db['host']) || empty($this->config->db['user']))
				{
					echo "<!-- missing db['host'] or db['user'] -->";
					exit;
				}
				if (!empty($this->config->db['socket'])) $separator = ":";
				$this->conn	= mysql_connect($this->config->db['host'] . $separator . $this->config->db['socket'], $this->config->db['user'], $this->config->db['pass']);
				if ($this->config->app['unicode']) $config_query = mysql_query("SET NAMES 'utf8'");
				mysql_select_db($this->config->db['name'], $this->conn);
			}
			
			if (empty($this->conn))
			{
				echo "<!-- Can't connect to the database. -->";
				exit;
			}
			return true;
		}
		
		// ================================================================== //
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $sql_string
		 * @param unknown_type $casting_object
		 * @return unknown
		 */
		function GetObjectArray($sql_string='', $casting_object='')
		{
			$data = array();
			if (empty($sql_string) || empty($casting_object)) return $data;
			$recordset = $this->ExecuteQuery($sql_string);
			if ($recordset) while ($record = mysql_fetch_assoc($recordset)) $data[] = Caster::Cast($record, $casting_object);
			return $data;
		}
		
		// ================================================================== //
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $sql_string
		 * @param unknown_type $casting_object
		 * @return unknown
		 */
		function GetObject($sql_string='', $casting_object='')
		{
			if (empty($sql_string) || empty($casting_object)) return false;
			$recordset = $this->ExecuteQuery($sql_string);
			if (mysql_num_rows($recordset) > 0) return Caster::Cast(mysql_fetch_assoc($recordset), $casting_object);
			else return false;
		}
		
		// ================================================================== //
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $sql_string
		 * @param unknown_type $value_name
		 * @return unknown
		 */
		function GetValue($sql_string='', $value_name='')
		{
			if (empty($sql_string) || empty($value_name)) return false;
			$recordset = $this->ExecuteQuery($sql_string);
			$record = mysql_fetch_assoc($recordset);
			return $record[$value_name];
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>