<?
	// ====================================================================== //
	
	class Thumbnail
	{
		// ================================================================== //
		
		var $height;
		var $width;
		var $target_width;
		var $target_height;

		var $crop;
		
		// ================================================================== //
		
		/**
		 * Enter description here...
		 *
		 * @param String $file
		 * @param int $target_width
		 * @param int $target_height
		 * @param Boolean $crop
		 * @return Thumbnail
		 */
		function Thumbnail($file, $target_name, $target_width, $target_height, $crop=false)
		{
			$this->SetFile($file);
			$this->SetTargetName($target_name);
			$this->SetTargetWidth($target_width);
			$this->SetTargetHeight($target_height);
			$this->SetCrop($crop);
		}
		
		// ================================================================== //

		function GetFile() { return $this->file; }
		function GetFileName()
		{
			$components = explode('/', $this->file);
			return $components[count($components)-1];
		}
		function GetTargetName() { return $this->target_name; }
		function GetTargetFileName() { return $this->target_name . "_" . $this->GetFileName(); }
		function GetTargetFile()
		{
			$components = explode('/', $this->file);
			$components[count($components)-1] = $this->GetTargetFileName();
			$this->targetfile = '';
			$separator = '';
			foreach ($components as $component)
			{
				$this->targetfile .= $separator . $component;
				$separator = '/';
			}
			return $this->targetfile;
		}
		
		function GetImageSize()
		{
			list($width, $height, $type, $attr) = getimagesize($this->GetFile());
			$this->SetWidth($width);
			$this->SetHeight($height);
		}
		
		function GetWidth()
		{
			if (empty($this->width)) $this->GetImageSize();
			return $this->width;
		}
		
		function GetHeight()
		{
			if (empty($this->height)) $this->GetImageSize();
			return $this->height;
		}
		
		function GetTargetWidth() { return $this->target_width; }
		function GetTargetHeight() { return $this->target_height; }

		// ================================================================== //
		
		function SetFile($file='') { $this->file = $file; }
		function SetTargetName($target_name='') { $this->target_name = $target_name; }
		function SetWidth($width='') { $this->width = $width; }
		function SetHeight($height='') { $this->height = $height; }
		function SetCrop($crop='') { $this->crop = $crop; }
		function SetTargetWidth($target_width='') { $this->target_width = $target_width; }
		function SetTargetHeight($target_height='') { $this->target_height = $target_height; }
		
		// ================================================================== //

		function CheckGDVersion()
		{
			$gd2 = "";
			ob_start();
			phpinfo(8);
			$phpinfo = ob_get_contents();
			ob_end_clean();
	
			$phpinfo = strip_tags($phpinfo);
			$phpinfo = stristr($phpinfo, "gd version");
			$phpinfo = stristr($phpinfo, "version");
			preg_match('/\d/', $phpinfo, $gd);
			$gd2 = $gd[0];
	
			return $gd2;
		}
			
		// ================================================================== //
		
		function Save()
		{
			try
			{
				$gd2 = $this->CheckGDVersion();
				$src_img = ImageCreateFromJpeg($this->GetFile());
				if ($src_img == "") $src_img = ImageCreateFromGif($this->GetFile());
				if ($src_img == "") $src_img = ImageCreateFromPng($this->GetFile());
				if ($src_img == "") return false;
				
				if ($gd2 == "1")
				{
					$dst_img = ImageCreate($this->GetTargetWidth(), $this->GetTargetHeight());
					imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $this->GetTargetWidth(), $this->GetTargetHeight(), $this->GetWidth(), $this->GetHeight());
				}
				else if ($gd2 > 1)
				{
					$dst_img = ImageCreateTrueColor($this->GetTargetWidth(), $this->GetTargetHeight());
					imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $this->GetTargetWidth(), $this->GetTargetHeight(), $this->GetWidth(), $this->GetHeight());
				}
	
				if ($gd2 != "")
				{
					imagejpeg($dst_img, $this->GetTargetFile(), 100);
					chmod($this->GetTargetFile(), 0777);
					imagedestroy($dst_img); 
					imagedestroy($src_img);
				}
			}
			catch (Exception $e)
			{
				print($e);
			}
			
			return true;
		}
		
		// ================================================================== //
	}
	
	// ====================================================================== //
?>