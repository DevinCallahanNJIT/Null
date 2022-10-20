<!DOCTYPE html>
<html>
<body>

<nav>
	<a href='index.php'>Login</a> |
	<a href='register.php'>Register</a>
</nav>

<div>
    <h1>Login</h1>
    <form method="POST" action="">
	<div>
	    <label>Username:</label><br>
	    <input type="text" placeholder="Enter Username" name="username"  required><br><br>
	</div>
	<div>
	    <label>Password</label><br>
	    <input type="password" placeholder="Enter Password" name="password"  required><br><br>
	</div>
	<input type="submit" value="Login" name="submit" />
</div>
<?php
if(isset($_POST['submit'])&& !empty($_POST['username']) && !empty($_POST['password']))	//starts php when user clicks submit button
{

	$inputedusername= $_POST['username'];	//getting username from the form 
	$inputedpassword= $_POST['password'];	//getting password from the form
	require_once('/home/ubuntu/Null/lib/rabbitMQLib.inc');	//calls required files to connect to server

	$client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","Authentication");
	if (isset($argv[1]))
	{
		$msg = $argv[1];
	}
	else
	{
		$msg = "login info";
	}

	$time = time();
	$sessionId = SHA1($inputedusername . $time . $inputedpassword);

	$request = array();
	$request['type'] = "login";
	$request['username'] = $inputedusername;//sending username to server
	$request['password'] = $inputedpassword;//sending password to server
	$request['message'] = $msg;				//sending message to server
	$request['sessionid'] = $sessionId;		//sending session to server
	$response = $client->send_request($request);

	$code = implode(" ",$response);	//Turns $response into a string
	if (str_contains($code, 'Success'))	//See if response if successful
	{
		die(header("Location: home.php"));
	}
	else
	{
		echo "client received response: ".PHP_EOL;
		print_r($response);
		echo "\n\n";
	}


} 
?>

    </form>

</body>
</html>