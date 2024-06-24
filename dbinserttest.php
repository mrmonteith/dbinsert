<?php
# Created: 4-15-2024 - Mike Monteith
# Introduced OOP Traits to reduce duplicate code for inserts

	require_once "config.php";	
	require_once 'dbinsert.class.php';

	$cars['Car1']	= 'Mustang';
	$cars['Car2']	= 'Ecosport';
		
		print_r($cars); 
		//exit;				

		$instance	= new DBInsertClass();
		$Name	= 'cars_print';
		$Table		= 'test';
		// The insert needs 3 pieces, the Name(So it knows which log file to write), 
		//              the data array to be inserted, and which table the insert goes to,
		//              since table could be different. 
		$result = $instance->DBInsertFunction($Name, $cars , $Table); //


