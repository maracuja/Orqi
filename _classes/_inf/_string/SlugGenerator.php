<?php
	class SlugGenerator
	{
		function GetSlug($string='')
		{
			return strtolower(ereg_replace("[^A-Za-z0-9\-]", "-", str_replace(" ", "-", $string)));
		}
	}
?>