<?php
	class Request
	{
		function GetGet($value='') { return $_GET[$value]; }
		function GetPost($value='') { return $_POST[$value]; }
		function GetFile($value='') { return $_FILES[$value]; }
		
		/**
		 * This will forward to a specified URL. If you don't specify a URL then
		 * it will return you to the referer.
		 * 
		 * NB: Use $this->MakeLink() to create Orqi-friendly URLs
		 * NB: If the redirect sends you to a blank screen, you can view the HTML
		 * source to find out which file has output some content to the write buffer.
		 *
		 * @param ValidURL $goto
		 */
		function Redirect($goto='')
		{
			if(headers_sent($file, $line)) echo "<!-- the headers were already sent in $file on line $line... -->";
			if (empty($goto)) $goto = $_SERVER['HTTP_REFERER'];
			if (!empty($_SERVER['HTTPS'])) $goto = str_replace("http:", "https:", $goto);
			header("Location: " . $goto);
			exit;
		}
	}
?>