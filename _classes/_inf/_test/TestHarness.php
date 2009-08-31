<?php
	class TestHarness
	{
		public $tests = array();
		public $config;
		
		function __construct()
		{
			$this->config = Config::GetInstance();
		}
		
		public function GetSuite()
		{
			$this->LoadTests();
			$suite = new PHPUnit_Framework_TestSuite('Project');
			foreach ($this->tests as $test)
			{
				$suite->addTestSuite($test);
			}
			return $suite;
		}
		
		function LoadDirFiles($directory='')
		{
			$ignore_arr = array(".", "..", ".svn", "_mapping", "_validators", "_core", ".DS_Store");
			if ($handle = opendir($directory))
			{
				while (false !== ($file = readdir($handle)))
				{
					if (!in_array($file, $ignore_arr))
					{
						$this->tests[] = str_replace('.php', '', $file);
					}
				}
			}

			closedir($handle);
		}
		
		function LoadTests()
		{
			$this->LoadDirFiles($this->config->classes['test'] . '/' . $this->config->base['domain']);
			$this->LoadDirFiles($this->config->classes['test'] . '/' . $this->config->classes['domain']);
			$this->LoadDirFiles($this->config->classes['test'] . '/' . $this->config->base['mapper']);
			$this->LoadDirFiles($this->config->classes['test'] . '/' . $this->config->classes['mapper']);
			$this->LoadDirFiles($this->config->classes['test'] . '/' . $this->config->base['validator']);
			$this->LoadDirFiles($this->config->classes['test'] . '/' . $this->config->classes['validator']);
		}
	}