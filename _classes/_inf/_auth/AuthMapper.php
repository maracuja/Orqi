<?
	// ================================================================================= //
	
	class AuthMapper extends Mapper
	{
		// ============================================================================= //
		
		function ValidUser($username, $password)
		{
			if (empty($username) || empty($password)) return false;
			$sql_string = "
				select	*
				from	users
				where	username='" . $username . "'
				and		(md5word='" . $password . "' or md5word='" . md5($password) . "')
				and		activated=1
			";
			return $this->dao->GetObject($sql_string, new User());
		}
		
		// ============================================================================= //
	}
	
	// ================================================================================= //
?>