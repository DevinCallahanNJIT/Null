<?php

function connDB(){
    $dbservername = "localhost";
	$dbusername = "local";
	$dbpassword = "ChangeLater";
	$database = "ProjectNull";
	
	// Create Connection
	$connection = new mysqli($dbservername, $dbusername, $dbpassword, $database);

    if($connection->connect_error) {
		die("Connection failed: " . $connection->connect_error);
	}

    return $connection;
}

?>