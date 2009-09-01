<?php
	class Generator
	{
		public $config;
		
		function __construct()
		{
			$this->config = Config::GetInstance();
		}
		
		public function Run()
		{
			echo "<h2>Code Generator is running!</h2>";
			$templates = $this->GetTemplates();
			foreach ($templates as $template)
			{
				echo "<hr><h3>" . $template . "</h3>";
				$xml = simplexml_load_file($template);
				
				foreach ($xml->file as $file)
				{
					// foreach object in the system ... do this ...
					echo "<h4>" . $file->info->location . " / " . $file->info->name . "</h4>";
					foreach ($file->section as $section)
					{
						$text = (string)$section->template;
						$text = str_replace("\n\t\t\t\t", "\n", $text);
						echo "<pre>" . $text . "</pre>"; 
					}
				}
			}
		}
		
		function GetTemplates()
		{
			$templates = array();
			if (is_dir($this->config->classes['generator']))
			{
				$ignore_arr = array(".", "..", ".svn", "_mapping", "_validators", "_core", ".DS_Store");
				if ($handle = opendir($this->config->classes['generator']))
				{
					while (false !== ($file = readdir($handle)))
					{
						if (!in_array($file, $ignore_arr))
						{
							$templates[] = $this->config->classes['generator'] . '/' . $file;
						}
					}
				}
				closedir($handle);
			}
			return $templates;
		}
		
		function CheckTargetDirectory()
		{
		}
		
		function ParseXML()
		{
		}
	}
