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
	$database = "ProjectNull";
	
	// Create Connection
	$connection = new mysqli($dbservername, $dbusername, $dbpassword, $database);
	
	// Check Connection
	if($connection->connect_error) {
		die("Connection failed: " . $connection->connect_error);
	}
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM User WHERE username = ? AND passwordHash = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ss", $username, $password);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();
	
	//Check Credentials
	$numRows = mysqli_num_rows($result);
	
	//If there is more than 0 results, the credentials match.
	if($numRows != 0){
		echo "Login Success, Creating Session \n".PHP_EOL;

		$datetime = date('Y-m-d H:i:s', time() + 86400);	//current date and time (example format: '2022-20-2022 18:46:26')
		$sessionID = hash('sha256', $username . $datetime); //session hash based on username and current time

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO Session VALUES(?, ?, ?)');
		//Binds Username Into Query Statement
		$query->bind_param("sss", $sessionID, $username, $datetime);	

		echo "about to run insert".PHP_EOL;

		//Execute Query
		if ($query->execute()) {
			echo "Session Successfully Created".PHP_EOL;
			return array(	
							"returnCode" => "202", 
							"message"=>"Login and Session Success: Authentication was successful and Session information has successfully been stored.",
							"sessionID"=>$sessionID,
							"username"=>$username,
							"expiration"=>$datetime
						);
		}
		
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data".PHP_EOL;
			return array("returnCode" => "404", "message"=>"Database Error: Logged in, failed to store session in db.");
		}

		return array("returnCode" => "202", "message"=>"Login Success: The user's credentials have been authenticated.");
	}else{
		echo "Login Failure \n".PHP_EOL;
		return array("returnCode" => "401", "message"=>"Login Failure: The username and/or password are incorrect.");	
	}
}

function doRegistration($username, $password)
{
	//Connect to database
	echo "Registration Began".PHP_EOL;
	$dbservername = "localhost";
	$dbusername = "local";
	$dbpassword = "ChangeLater";
	$database = "ProjectNull";
	
	// Create Connection
	$connection = new mysqli($dbservername, $dbusername, $dbpassword, $database);
	
	// Check Connection
	if($connection->connect_error) {
		die("Connection failed: " . $connection->connect_error);
	}

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM User WHERE username = ?');
	//Binds Username Into Query Statement
	$query->bind_param("s", $username);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	
	//Check Username Availability
	$numRows = mysqli_num_rows($result);
	
	//If username unavailable, failure; else, insert username and password
	if($numRows != 0){
		echo "Registration Failure \n".PHP_EOL;
		return array("returnCode" => "401", "message"=>"Registration Failure: This username is already taken.");
	}else{
		//Query Insert Data
		$query = $connection->prepare('INSERT INTO User VALUES(?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ss", $username, $password);	
		
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Account Successfully Registered \n".PHP_EOL;
			return array("returnCode" => "202", "message"=>"Registration Success: Your account has successfully been processed.");
		 }
		 //If the last query failed and gave an error, return an error
		 if ($connection->errno) {
			echo "Database Error: Failed To Insert Data \n".PHP_EOL;
			return array("returnCode" => "404", "message"=>"Database Error: Failed to insert data into database.");
		 }
	}
}

function requestProcessor($request)
{
	echo "Received Request".PHP_EOL;
	
	//DEBUG CODE - 1 LINE: show incoming array
	var_dump($request);

	if(!isset($request['type']))
	{
		return "ERROR: unsupported message type";
	}
	switch ($request['type'])
	{
		case "login":
			return doLogin($request['username'],$request['password']);

		case "register":
			return doRegistration($request['username'],$request['password']);

		

	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("../lib/RabbitMQ.ini","Authentication");

echo "RabbitMQServer BEGIN \n".PHP_EOL;
$server->process_requests('requestProcessor');
echo "RabbitMQServer END \n".PHP_EOL;
exit();
?>