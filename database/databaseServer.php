!/usr/bin/php
<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');

function doLogin($username,$password)
{
	echo "Authentication Began".PHP_EOL;
	$dbservername = "localhost";
	$dbusername = "local";
	$dbpassword = "ChangeLater";
	$database = "test_db";
	
	// Create Connection
	$connection = new mysqli($dbservername, $dbusername, $dbpassword, $database);
	
	// Check Connection
	if($connection->connect_error) {
		die("Connection failed: " . $connection->connect_error);
	}
	
	//Query Credentials
	$query = "SELECT * FROM account WHERE username = \"".$username."\" AND password = \"".$password."\" LIMIT 1";
	
	$result = $connection->query($query);
	
	//Check Credentials
	$numRows = mysqli_num_rows($result);
	
	if($numRows != 0){
		echo "Login Success".PHP_EOL;
		return array("returnCode" => "202", "message"=>"Login Success: This is a place holder for a session token.");
	}else{
		echo "Login Failure".PHP_EOL;
		return array("returnCode" => "401", "message"=>"Login Failure: The username and/or password are incorrect.");	
	}

	// check password
	return true;
	//return false if not valid
}

function requestProcessor($request)
{
	echo "Received Request".PHP_EOL;
	var_dump($request);
	if(!isset($request['type']))
	{
		return "ERROR: unsupported message type";
	}
	switch ($request['type'])
	{
	case "login":
		return doLogin($request['username'],$request['password']);
	case "validate_session":
		return doValidate($request['sessionId']);
	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("../lib/RabbitMQ.ini","Authentication");

echo "RabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "RabbitMQServer END".PHP_EOL;
exit();
?>
