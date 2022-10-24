<!DOCTYPE html>
<html>
<body>

<nav>
	<a href='index.php'>Login</a> |
	<a href='register.php'>Register</a>
</nav>

<div>
    <h1>Register</h1>
    <form method="POST" action="">
	<div>
	    <label>Username:</label><br>
	    <input type="text" placeholder="Enter Username" name="username"  required><br><br>
	</div>
	<div>
	    <label>Password</label><br>
	    <input type="password" placeholder="Enter Password" name="password"  required><br><br>
	</div>
	<input type="submit" value="Register" name="submit" />
</div>
<?php
if(isset($_POST['submit']))	//starts php when user clicks submit button
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

	//generate password hash with salt
	$salt = substr(hash('sha256', $inputedusername), 5, 15);
	$passHash = hash('sha256', $salt.$inputedpassword);


	$request = array();
	$request['type'] = "register";
	$request['username'] = $inputedusername;//sending username to server
	$request['password'] = $passHash;//sending password to server
	$response = $client->send_request($request);
	//$response = $client->publish($request);

	$code = implode(" ",$response);	//Turns $response into a string
	if (str_contains($code, 'Success'))	//See if response if successful
	{
		die(header("Location: index.php"));
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
