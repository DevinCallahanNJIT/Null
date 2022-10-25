<!DOCTYPE html>
<html>
<body>

<nav>
    <a href='home.php'>Home</a> |
    <a href='logout.php'>Logout</a>
</nav>

<div>
    <h1>Home</h1>

<?php
    if(isset($_COOKIE["Session"])){
        echo "Session Cookie Is Set!\n";
        require_once('/home/ubuntu/Null/lib/rabbitMQLib.inc');	//calls required files to connect to server
        $client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","Authentication"); //connect to authentication queue

        $cookieArray = (array) json_decode( $_COOKIE["Session"]);//decode cookie value (value is a json encoded array)

        //create array of request data
        $request = array();
        $request['type'] = "session";
        $request['sessionID'] = $cookieArray['sessionID'];
        $request['username'] = $cookieArray['username'];
        $request['expiration'] = $cookieArray['expires'];

        //send $request to database server, listen for and store response
        $response = $client->send_request($request);

        $code = implode(" ",$response);	//Turns $response into a string
        if (str_contains($code, 'Success'))	//See if response if successful
        {
            echo "<h3>Welcome ".$cookieArray['username']."</h3>";
        }else{
            echo "<h3>Welcome Guest</h3>";
        }

    }
?>

</div>

</body>
</html>
