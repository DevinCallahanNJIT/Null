<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');
require_once('./logging.php');

function doLogin($username,$password)
{
	echo "Authentication Began".PHP_EOL;
	logging("Authentication Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
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
		echo "Authentication Success\nCreating Session".PHP_EOL;
		logging("Authentication Success", __FILE__);
		logging("Creating Session", __FILE__);

		$datetime = date('Y-m-d H:i:s', time() + 86400);	//current date and time (example format: '2022-20-2022 18:46:26')
		$sessionID = hash('sha256', $username . $datetime); //session hash based on username and current time

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO Session VALUES(?, ?, ?)');
		//Binds Username Into Query Statement
		$query->bind_param("sss", $sessionID, $username, $datetime);	

		//Execute Query
		if ($query->execute()) {
			echo "Session Successfully Created".PHP_EOL;
			logging("Session Successfully Created", __FILE__);
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
			echo "Database Error: Failed To Insert Data \n".PHP_EOL;
			loggingWarn("Database Error: Failed to Insert Data", __FILE__);
			return array("returnCode" => "404", "message"=>"Database Error: Logged in, failed to store session in db.");
		}
		logging("User's credentials have been authenticated", __FILE__);
		return array("returnCode" => "202", "message"=>"Login Success: The user's credentials have been authenticated.");
	}else{
		echo "Login Failure \n".PHP_EOL;
		logging("Login Failure: username and password are incorrect", __FILE__);
		return array("returnCode" => "401", "message"=>"Login Failure: The username and/or password are incorrect.");	
	}
}

function doRegistration($username, $password)
{
	echo "Registration Began".PHP_EOL;
	logging("Registration Began", __FILE__);

	// Create Connection
	$connection = connDB();

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
		loggingWarn("Registration Failure: Username already exists", __FILE__);
		return array("returnCode" => "401", "message"=>"Registration Failure: This username is already taken.");
	}else{
		//Query Insert Data
		$query = $connection->prepare('INSERT INTO User VALUES(?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ss", $username, $password);	
		
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Account Successfully Registered \n".PHP_EOL;
			logging("Account Successfully Registered", __FILE__);
			return array("returnCode" => "202", "message"=>"Registration Success: Your account has successfully been processed.");
		 }
		 //If the last query failed and gave an error, return an error
		 if ($connection->errno) {
			echo "Database Error: Failed To Insert Data \n".PHP_EOL;
			loggingWarn("Database Error: Failed To Insert Data", __FILE__);
			return array("returnCode" => "404", "message"=>"Database Error: Failed to insert data into database.");
		 }
	}
}

function doSession($sessionID, $username, $expiration){

	echo "Session Validation Began".PHP_EOL;
	logging("Session Validation Began", __FILE__);

	// Create Connection
	$connection = connDB();

	//reformat $expiration into unix timestamp
	$timestamp = strtotime($expiration);

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Session WHERE sessionID = ? AND username = ? AND expiration = ? LIMIT 1');
	//Binds Username Into Query Statement
	$query->bind_param("sss", $sessionID, $username, $expiration);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//Check for matching row
	$numRows = mysqli_num_rows($result);
	
	//If valid Session data is found, check expiration date and time for expiration, else, session validation failed
	if($numRows != 0){
		
		//if expiration time is valid, success, else, failure
		if (time()<=$timestamp){
			echo "Session Validation Success\n".PHP_EOL;
			logging("Session Validation Success", __FILE__);
			return array("returnCode" => "202", "message"=>"Session Validation Success: The session information is valid.");
		}else{
			echo "Session Validation Failure\n".PHP_EOL;
			loggingWarn("Session Validation Failure: session expired", __FILE__);
			return array("returnCode" => "401", "message"=>"Session Validation Failure: The session is expired.");
		}
	}else{
		echo "Session Validation Failure\n".PHP_EOL;
		loggingWarn("Session Validation Failure: The session ID is invalid", __FILE__);
		return array("returnCode" => "401", "message"=>"Session Validation Failure: The session ID is invalid.");
	}

}

function requestProcessor($request)
{
	echo "\n[Received Request]".PHP_EOL;
	logging("Received an auth request", __FILE__);
	
	//var_dump($request);

	if(!isset($request['type']))
	{
		return "ERROR: unsupported message type";
		loggingWarn("Unsupported message type", __FILE__);
	}
	switch ($request['type'])
	{
		case "login":
			return doLogin($request['username'],$request['password']);

		case "register":
			return doRegistration($request['username'],$request['password']);

		case "session":
			return doSession($request['sessionID'],$request['username'], $request['expiration']);

		

	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("../lib/rabbitMQ.ini","Authentication");

echo "Authentication Server BEGIN \n".PHP_EOL;
logging("Authentication Server BEGIN", __FILE__);
$server->process_requests('requestProcessor');
echo "Authentication Server END \n".PHP_EOL;
logging("Authentication Server END", __FILE__);
exit();
?>