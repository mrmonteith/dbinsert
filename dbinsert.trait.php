<?php
// File: dbinsert.trait.php - Use it to do all the insert and logging for the
//          label scripts
//  This dbisert trait was meant to be more generic to use in other classes. 
//  *** Need to check for empty data in the function ***
// Created: 4-15-2024
// By: Michael Monteith
trait DBInsertTrait {
    public function DBInsertFunction( $name, $data, $table)
    {
        require "config.php"; # Cannot be a require once 
        //print_r($data); exit;				
        $column_names = implode(", ", array_keys($data));	
        $column_values = implode("', '", array_values($data));		
        $query  = "INSERT INTO " . $servername . "." . $table . " (" . $column_names . ") VALUES ('" . $column_values . "')";
        echo $query; 
        exit;

        //$servername = 'test';  // This is here to test if a PDO failure to check output
        // mail('username@gmail.com', $labelname ' query', $query);
        try
        {
            // *** Need to change the prepared statement to a proper PDO prepare for security reasons
            $pdo = new PDO("odbc:Driver={IBM i Access ODBC Driver 64-bit}; System={$servername}; Database={$dbname};", $username, $password);
            $stmt = $pdo->prepare($query); 
            $stmt->execute();
            if($stmt->rowCount() == 1)
                $result = "SUCCESS";
        } catch(PDOException $e)
        {
            $error = "PDO Error: ". $e->getMessage();
            $result = "FAIL   ";
        }		
        
        if($stmt)
        {		
            $_SESSION['message_status'] = 1;					
            $_SESSION['message'] = 'Your label has been sent to the printer.';				
        }
        else
        {
            echo"Error: ". $e->getMessage();	// Might be able to add it to the tail of the session message below instead			
            $_SESSION['message_status'] = 0;					
            $_SESSION['message'] = 'Your label has <strong>NOT</strong> been sent to the printer.';				
        }

        // Write to a log file so we can use for looking up issues later
        date_default_timezone_set('America/New_York');
        $today = date("Y-m-d h:i:s A T ");
        $rootPath = $_SERVER['DOCUMENT_ROOT']; // Makes the path relative with where scripts are installed
        file_put_contents($rootPath . '/logs/' . $name . '.log', $today . "- " . $result . " - " . $query . "\r\n", FILE_APPEND);
        
        if($error != NULL)
            file_put_contents($rootPath . '/logs/' . $name . '.log', $today . "- " . $error  . "\r\n", FILE_APPEND);

        $pdo = NULL;  // *** Close Database Connection ***
        return $result; // 
    }
}