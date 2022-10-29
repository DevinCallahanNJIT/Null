<?php

require_once(__DIR__ . '/../../lib/rabbitMQLib.inc');
require_once('logging.php');

function isLoggedIn(){
    return isset($_COOKIE['Session']);
}

function getUsername(){
    $cookieArray = (array) json_decode( $_COOKIE["Session"]);
    return $cookieArray['username'];
}

function getURL($path){
    if (substr($path, 0, 1) == "/") {
        return $path;
    }
    //return $_SERVER["CONTEXT_PREFIX"] . "/~ji64/$path";
}

function rabbitAuthClient($request){
    $client = new rabbitMQClient("/home/ubuntu/Null/lib/rabbitMQ.ini","Authentication");
    $response = $client->send_request($request);
    return $response;
}

function isSessionValid(){
    $cookieArray = (array) json_decode( $_COOKIE["Session"]);

    $request = array();
    $request['type'] = "session";
    $request['sessionID'] = $cookieArray['sessionID'];
    $request['username'] = $cookieArray['username'];
    $request['expiration'] = $cookieArray['expires'];

    $response = rabbitAuthClient($request);
    $code = implode(" ",$response);	//Turns $response into a string
    if (str_contains($code, 'Success')) { //See if response if successful
        return true;
    }	
    
    return false;


}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

?>