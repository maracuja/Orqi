<?php
	class DebuggingFilter
	{
		public $config;
		public $session;
		
		function DebuggingFilter()
		{
			$this->config = Config::GetInstance();
			$this->session = new Session();
		}
		
		function Execute()
		{
//			echo "<!-- ";
//			print_r($_GET);
//			print_r($_POST);
//			print_r($_FILES);
//			print_r($this->session);
//			print_r($this->config);
//			echo " -->";
		}
		
		function Terminate() {}
	}
?>