<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>

<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container p-5"> 
	<form method="POST">
	<div class="mb-3 mt-3">
		<label class="form-label">Username:</label>
		<input type="text" class="form-control" placeholder="Enter username" name="username" required>
	</div>
	<div class="mb-3">
		<label class="form-label">Password:</label>
		<input type="password" class="form-control" placeholder="Enter Password" name="password" required>
	</div>
	<div class="mb-3">
		<label class="form-label">Confirm Password:</label>
		<input type="password" class="form-control" placeholder="Confirm Password" name="confirm" required>
	</div>
	<input type="submit" value="Register" class="btn btn-primary" name="submit"/>
	</form>
	<p></p>
    <p class="h6">Have an account? <a href="login.php">Login Here </a></p>

</div>

<?php
if(isset($_POST['submit'])){	//starts php when user clicks submit button
	
	$username = null;
	$password = null;
	$confirm = null;

	if (isset($_POST["username"])) {
		$username = $_POST["username"];
	}
	if (isset($_POST["password"])) {
		$password = $_POST["password"];
	}
	if (isset($_POST["confirm"])) {
		$confirm = $_POST["confirm"];
	}

	$isValid = true;

	if ($password == $confirm) {
	}
	else {
		$isValid = false;
	}
	if (!isset($username) || !isset($password) || !isset($confirm)) {
		$isValid = false;
	}



	if($isValid){
		//generate password hash with salt
		$salt = substr(hash('sha256', $username), 5, 15);
		$passHash = hash('sha256', $salt.$password);

		//create array of request data
		$request = array();
		$request['type'] = "register";
		$request['username'] = $username;//sending username to server
		$request['password'] = $passHash;//sending hashed password to server
		
		$response = $response = rabbitAuthClient($request);//send $request and wait to store response in $response

		$code = implode(" ",$response);	//Turns $response into a string
		safer_echo($code);

		if (str_contains($code, 'Success'))	//See if response if successful
		{
			die(header("Location: login.php"));
		}
		else
		{
			errorLog($response);
		}
	}

	if (!isset($username)) {
        $username = "";
    }
	

	


} 
?>



</body>
</html>
