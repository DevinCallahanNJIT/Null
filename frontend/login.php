<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
    <?php require_once(__DIR__ . '/partials/navbar.php'); ?>

    <div class="container p-5"> 
        <form method="POST" action="">
        <div class="mb-3 mt-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" class="form-control" id="username" placeholder="Enter username" name="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
        </div>
        <input type="submit" value="Login" class="btn btn-primary" name="submit"/>
        </form>
        <p></p>
        <p class="h6">Don't have an account? <a href="register.php">Register Here </a></p>


    </div>

<?php
if(isset($_POST['submit']))	{ //starts php when user clicks submit button

    $username = null;
    $password = null;

    if (isset($_POST["username"])) {
        $username = $_POST["username"];
    }
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    $isValid = true;
    if (!isset($username) || !isset($password)) {
        $isValid = false;
    }

	//generate password hash with salt
	$salt = substr(hash('sha256', $username), 5, 15);
	$passHash = hash('sha256', $salt.$password);


	//create array of request data
	$request = array();
	$request['type'] = "login";
	$request['username'] = $username;//sending username to server
	$request['password'] = $passHash;//sending hashed password to server
	
	$response = rabbitAuthClient($request);//send $request and wait to store response in $response
    
	$code = implode(" ",$response);	//Turns $response into a string

    safer_echo($code);

	if (str_contains($code, 'Success'))	//See if response if successful
	{
        $cookiePath = "/";

		$cookieArray = array('sessionID'=>$response['sessionID'], 'username'=>$response['username'], 'expires'=>$response['expiration']);

        setcookie("Session", json_encode($cookieArray), $cookieExpiration, $path);

		die(header("Location: home.php"));
	}
	else
	{
		safer_echo($response);
	}


} 
?>



</body>
</html>
