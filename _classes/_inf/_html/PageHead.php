<?php
	class PageHead
	{
		public $title;
		public $keywords;
		public $description;
		
		public $config;
		
		function PageHead($title='', $keywords='', $description='')
		{
			$this->config = Config::GetInstance();
			
			$this->title = $title;
			$this->keywords = $keywords;
			$this->description = $description;
		}
		
		function GetTitle() { return (empty($this->title)) ? $this->config->app['title'] . " - " . Request::GetGet('object') . " - " . Request::GetGet('action') . " - " . Request::GetGet('id') : $this->title; }
		function GetKeywords() { return $this->keywords; }
		function GetDescription() { return $this->description; }
		
		function SetTitle($title='') { $this->title = $title; }
		function SetKeywords($keywords='') { $this->keywords = $keywords; }
		function SetDescription($description='') { $this->description = $description; }
	}
?>