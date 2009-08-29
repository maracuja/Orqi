<?php
	class ePDQ
	{
		public function GetEncryptedData($order_id, $order_total)
		{
			$config = Config::GetInstance();
			
			#the following parameters have been obtained earlier in the merchant's webstore
			#clientid, passphrase, oid, currencycode, total
			$params = "clientid=" . $config->epdq['clientid'];
			$params .= "&password=" . $config->epdq['passphrase'];
			$params .= "&oid=" . $order_id;
			$params .= "&chargetype=Auth";
			$params .= "&currencycode=" . $config->epdq['currencycode'];
			$params .= "&total=$order_total";
			
			#perform the HTTP Post
			$response = self::pullpage($config->epdq['enc_server'], $config->epdq['enc_url'], $params);
			
			#split the response into separate lines
			$response_lines=explode("\n",$response);
			
			#for each line in the response check for the presence of the string 'epdqdata'
			#this line contains the encrypted string
			$response_line_count=count($response_lines);
			for ($i=0;$i<$response_line_count;$i++){
			    if (preg_match('/epdqdata/',$response_lines[$i])){
			        $strEPDQ=$response_lines[$i];
			    }
			}
			return $strEPDQ;
		}
		
		private function pullpage( $host, $usepath, $postdata = "" )
		{
			# open socket to filehandle(epdq encryption cgi)
			$fp = fsockopen( $host, 80, $errno, $errstr, 60 );
			
			#check that the socket has been opened successfully
			if( !$fp ) return '';
			else
			{
				#write the data to the encryption cgi
				fputs( $fp, "POST $usepath HTTP/1.0\n");
				$strlength = strlen( $postdata );
				fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
				fputs( $fp, "Content-length: ".$strlength."\n\n" );
				fputs( $fp, $postdata."\n\n" );
				
				# clear the response data
				$output = "";
				# read the response from the remote cgi 
				# while content exists, keep retrieving document in 1K chunks
				while( !feof( $fp ) ) $output .= fgets( $fp, 1024);
				
				# close the socket connection
				fclose( $fp);
			}
			#return the response
			return $output;
		}
	}
?>