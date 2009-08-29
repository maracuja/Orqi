<?php
	class ResizedImage
	{
		public $config;
		
		public $filename;
		public $label;		// this is the image label in _config/settings.yml
		public $orientation;// L|R|C + T|M|B
		public $scale;		// crop|shrink|stretch|fit
		public $cropcoords;
		public $overlay;
		
		public $imagewidth;
		public $imageheight;
		public $target_width;
		public $target_height;
		
		public $gd;
		
		function ResizedImage($filename='', $prefix='', $orientation='', $scale='', $target_width='', $target_height='', $cropcoords='', $overlay='')
		{
			$this->config = Config::GetInstance();
			
			$this->filename = $filename;
			$this->prefix = $prefix;
			$this->orientation = $orientation;
			$this->scale = $scale;
			$this->target_width = $target_width;
			$this->target_height = $target_height;
			$this->cropcoords = $cropcoords;
			$this->overlay = $overlay;
		}
		
		function GetFilename() { return $this->filename; }
		function GetPrefix() { return $this->prefix; }
		function GetOrientation() { return $this->orientation; }
		function GetScale() { return $this->scale; }
		function GetTargetWidth() { return $this->target_width; }
		function GetTargetHeight() { return $this->target_height; }
		function GetTargetAspectRatio() { return $this->target_width / $this->target_height; }
		function GetOverlay() { return $this->overlay; }
		
		function SetFilename($filename='') { $this->filename = $filename; }
		function SetPrefix($prefix='') { $this->prefix = $prefix; }
		function SetOrientation($orientation='') { $this->orientation = $orientation; }
		function SetScale($scale='') { $this->scale = $scale; }
		function SetTargetWidth($target_width='') { $this->target_width = $target_width; }
		function SetTargetHeight($target_height='') { $this->target_height = $target_height; }
		function SetOverlay($overlay='') { $this->overlay = $overlay; }

		function GetImageSize()
		{
			list($width, $height, $type, $attr) = getimagesize($this->GetImageFullPath());
			$this->SetImageWidth($width);
			$this->SetImageHeight($height);
		}
		
		function SetImageWidth($width='') { $this->imagewidth = $width; }
		function SetImageHeight($height='') { $this->imageheight = $height; }
		function GetImageWidth()
		{
			if (empty($this->imagewidth)) $this->GetImageSize();
			return $this->imagewidth;
		}
		
		function GetImageHeight()
		{
			if (empty($this->imageheight)) $this->GetImageSize();
			return $this->imageheight;
		}
		function GetImageAspectRatio() { return $this->GetImageWidth() / $this->GetImageHeight(); }
		function GetImageFullPath() { return $this->config->resource['path'] . $this->GetFileName(); }
		
		function GetResizedImageFileName()
		{
			if (!empty($this->overlay))
			{
				$fileparts = explode('.', $this->filename);
				array_pop($fileparts);
				$this->filename = implode('.', $fileparts) . '.gif';
			}
			if (strpos($this->filename, "_"))
			{
				list($prefix, $filename) = explode("_", $this->filename);
				return "/" . $this->prefix . "_" . $filename;
			}
			else return "/" . $this->prefix . "_" . str_replace("/", "", $this->filename);
		}
		function GetResizedImageFullPath() { return $this->config->resource['path'] . $this->GetResizedImageFileName(); }
		function GetResizedImageFullUrl() { return $this->config->resource['url'] . $this->GetResizedImageFileName(); }

		// ================================================================== //

		function SetGDVersion()
		{
			$this->gd = "";
			ob_start();
			phpinfo(8);
			$phpinfo = ob_get_contents();
			ob_end_clean();
	
			$phpinfo = strip_tags($phpinfo);
			$phpinfo = stristr($phpinfo, "gd version");
			$phpinfo = stristr($phpinfo, "version");
			preg_match('/\d/', $phpinfo, $this->gd);
			$this->gd = $this->gd[0];
		}
		
		function LoadSourceImage()
		{
			$source_image = ImageCreateFromJpeg($this->GetImageFullPath());
			if ($source_image == "") $source_image = ImageCreateFromGif($this->GetImageFullPath());
			if ($source_image == "") $source_image = ImageCreateFromPng($this->GetImageFullPath());
			if ($source_image == "") $source_image = false;
			return $source_image;
		}
		
		function LoadOverlay()
		{
			$source_image = ImageCreateFromJpeg($this->config->resource['imgpath'] . $this->overlay);
			if ($source_image == "") $source_image = ImageCreateFromGif($this->config->resource['imgpath'] . $this->overlay);
			if ($source_image == "") $source_image = ImageCreateFromPng($this->config->resource['imgpath'] . $this->overlay);
			if ($source_image == "") $source_image = false;
			return $source_image;
		}
		
		function CropSource($source_image='')
		{
			list($x1, $y1, $x2, $y2) = explode("|", $this->cropcoords);
			$new_source_image = $this->CreateImage($x2-$x1, $y2-$y1);
			imagecopyresized($new_source_image, $source_image, 0, 0, $x1, $y1, $x2-$x1, $y2-$y1, $x2-$x1, $y2-$y1);
			$this->SetImageWidth($x2-$x1);
			$this->SetImageHeight($y2-$y1);
			return $new_source_image;
		}
		
		function CreateImage($width='', $height='')
		{
			if ($this->gd == "1")	return ImageCreate($width, $height);
			else if ($this->gd > 1)	return ImageCreateTrueColor($width, $height);
		}
		
		function OverlayImages($destination_image, $source_image, $dest_x, $dest_y, $source_x, $source_y, $dest_w, $dest_h, $source_width, $source_height)
		{
			if ($this->gd == "1")
			{
				imagecopyresized($destination_image, $source_image, $dest_x, $dest_y, $source_x, $source_y, $dest_w, $dest_h, $source_width, $source_height);
			}
			else if ($this->gd > 1)
			{
				imagecopyresampled($destination_image, $source_image, $dest_x, $dest_y, $source_x, $source_y, $dest_w, $dest_h, $source_width, $source_height);
			}
		}
		
		function GetAdjustedHorizontal()
		{
			$width = $this->GetTargetHeight() * $this->GetImageWidth() / $this->GetImageHeight();
			switch ($this->orientation[0])
			{
				case "R":
					$x = $this->GetTargetWidth() - $width;
					break;
					
				case "C":
					$x = round(($this->GetTargetWidth() - $width) / 2);
					break;
					
				case "L":
					$x = 0;
					break;
			}
			return array($x, $width);
		}
		
		function GetAdjustedVertical()
		{
			$height = $this->GetTargetWidth() * $this->GetImageHeight() / $this->GetImageWidth();
			switch ($this->orientation[1])
			{
				case "B":
					$y = $this->GetTargetHeight() - $height;
					break;
					
				case "M":
					$y = round(($this->GetTargetHeight() - $height) / 2);
					break;
					
				case "T":
					$y = 0;
					break;
			}
			return array($y, $height);
		}
			
		// ================================================================== //
		
		function Save()
		{
			try
			{
				$this->SetGDVersion();
				$source_image = $this->LoadSourceImage();
				if (!empty($this->cropcoords)) $source_image = $this->CropSource($source_image);
				$destination_image = $this->CreateImage($this->GetTargetWidth(), $this->GetTargetHeight());
				
				$dest_x = 0;
				$dest_y = 0;
				$newTargetHeight = $this->GetTargetHeight();
				$newTargetWidth = $this->GetTargetWidth();
				
				switch ($this->scale)
				{
					case "fit":
						if ($this->GetImageAspectRatio() < $this->GetTargetAspectRatio()) list($dest_y, $newTargetHeight) = $this->GetAdjustedVertical();
						if ($this->GetImageAspectRatio() > $this->GetTargetAspectRatio()) list($dest_x, $newTargetWidth) = $this->GetAdjustedHorizontal();
						$this->OverlayImages($destination_image, $source_image, $dest_x, $dest_y, 0, 0, $newTargetWidth, $newTargetHeight, $this->GetImageWidth(), $this->GetImageHeight());
						break;
						
					case "shrink";
						if ($this->GetImageAspectRatio() > $this->GetTargetAspectRatio()) list($dest_y, $newTargetHeight) = $this->GetAdjustedVertical();
						if ($this->GetImageAspectRatio() < $this->GetTargetAspectRatio()) list($dest_x, $newTargetWidth) = $this->GetAdjustedHorizontal();
						$this->OverlayImages($destination_image, $source_image, $dest_x, $dest_y, 0, 0, $newTargetWidth, $newTargetHeight, $this->GetImageWidth(), $this->GetImageHeight());
						break;
					
					case "crop":
						switch ($this->orientation[0])
						{
							case "R":	$dest_x = $this->GetTargetWidth() - $this->GetImageWidth();				break;
							case "C":	$dest_x = round(($this->GetTargetWidth() - $this->GetImageWidth()) / 2);	break;
						}
						switch ($this->orientation[1])
						{
							case "B":	$dest_y = $this->GetTargetHeight() - $this->GetImageHeight();					break;
							case "M":	$dest_y = round(($this->GetTargetHeight() - $this->GetImageHeight()) / 2);	break;
						}
						$this->OverlayImages($destination_image, $source_image, $dest_x, $dest_y, 0, 0, $this->GetImageWidth(), $this->GetImageHeight(), $this->GetImageWidth(), $this->GetImageHeight());
						break;
						
					case "stretch":
						$this->OverlayImages($destination_image, $source_image, 0, 0, 0, 0, $this->GetTargetWidth(), $this->GetTargetHeight(), $this->GetImageWidth(), $this->GetImageHeight());
						break;
				}
				
				if ($this->gd != "")
				{
					if (!empty($this->overlay))
					{
						$overlay_image = $this->LoadOverlay();
						$this->OverlayImages($destination_image, $overlay_image, 0, 0, 0, 0, $this->GetTargetWidth(), $this->GetTargetHeight(), $this->GetTargetWidth(), $this->GetTargetHeight());
						$transparent = imagecolorallocate($destination_image, 0, 0, 255);
						imagecolortransparent($destination_image, $transparent);
						imagegif($destination_image, $this->GetResizedImageFullPath(), 100);
					}
					else
					{
						imagejpeg($destination_image, $this->GetResizedImageFullPath(), 100);
					}
					chmod($this->GetResizedImageFullPath(), 0777);
					imagedestroy($destination_image); 
					imagedestroy($source_image);
				}
			}
			catch (Exception $e)
			{
				print($e);
			}
			
			return true;
		}
	}
?>