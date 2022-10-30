<?php

/*
	Nothing In This File Is Relevant Towards The System.
	This Code Exists Just To Test Parts Of Functions Being Developed Independently, And Is Changed Frequently.
	Code In This File May Exist As Incomplete Code And Should Not Be Referenced For Other Code.
*/

require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');

	$searchString = 'a';

	echo "Cocktail Search Began".PHP_EOL;

	// Create Connection
	$connection = connDB();
		
	$searchString = "%".$searchString."%";

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Cocktail WHERE cocktailName LIKE ?');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $searchString);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['cocktailID'.(string)$counter] = $resultData[0];
		$response['cocktailName'.(string)$counter] = $resultData[1];
		$response['publisher'.(string)$counter] = $resultData[2];
		$response['instructions'.(string)$counter] = $resultData[3];
		$response['imageRef'.(string)$counter] = $resultData[4];
		$counter++;
	}
    
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
    print_r($response);

//return nothing found

?>