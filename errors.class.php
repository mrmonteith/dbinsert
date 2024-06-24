<?php

	/*
	error_log() log types
	0	message is sent to PHP's system logger, using the Operating System's system logging mechanism or a file, depending on what the error_log configuration directive is set to. This is the default option.
	1	message is sent by email to the address in the destination parameter. This is the only message type where the fourth parameter, extra_headers is used.
	2	No longer an option.
	3	message is appended to the file destination. A newline is not automatically added to the end of the message string.
	4	message is sent directly to the SAPI logging handler.
	*/

	class errors
	{	
		static function log_to_server($_log_text)
		{		
			error_log($_log_text);		
		}		

		static function log_to_file($_log_text, $_log_path = '')
		{		
			//LOG_PATH is set in config.php
			//PHP_EOL is the correct end of line character 
			//(to create new line based on the system PHP is running on)
			if($_log_path == '')
			{			
				if(!file_exists(DEFAULT_LOG_PATH))
				{					
					//Create log file for today if it doesn't exist already.
					$log_file = fopen(DEFAULT_LOG_PATH, 'w');
					fclose($log_file);				
				}			
				error_log(date("m-d-Y H:i:s") . " " . $_log_text . PHP_EOL, 3, DEFAULT_LOG_PATH);			
			}
			else
			{			
				error_log($_log_text . PHP_EOL, 3, $_log_path);			
			}
		}		
		
		static function log_to_email($_log_text, $_email_address)
		{		
			error_log($_log_text, 1, $_email_address);		
		}	
	}

?>