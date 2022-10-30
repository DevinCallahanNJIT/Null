<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');

function fetchLiquor($liquorID)
{
	echo "Liquor Search Began".PHP_EOL;

	// Create Connection
	$connection = connDB();
		
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Liquor WHERE liquorID = ?');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $liquorID);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['liquorID'.(string)$counter] = $resultData[0];
		$response['liquorName'.(string)$counter] = $resultData[1];
		$response['imageRef'.(string)$counter] = $resultData[2];
		$counter++;
	}
    
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
    return $resposne;
	
}

function fetchCocktail($cocktailID){

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
    return $resposne;

}


function requestProcessor($request)
{
	echo "\nReceived Request".PHP_EOL;
	
	//var_dump($request);

	if(!isset($request['type']))
	{
		return "ERROR: unsupported message type";
	}
	switch ($request['type'])
	{
		case "liquor":
			return fetchLiquor($request['liquorID']);

		case "cocktail":
			return fetchCocktail($request['cocktailID']);

	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("../lib/rabbitMQ.ini","DatabaseSearch");

echo "RabbitMQServer BEGIN \n".PHP_EOL;
$server->process_requests('requestProcessor');
echo "RabbitMQServer END \n".PHP_EOL;
exit();
?>