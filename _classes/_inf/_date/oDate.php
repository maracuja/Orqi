<?php
	class oDate
	{
		function String2Time($value)
		{
			list($day, $month, $year) = explode("/", $value);
			return strtotime("$month/$day/$year");
		}
	}
?>