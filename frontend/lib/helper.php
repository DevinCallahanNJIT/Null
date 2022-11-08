<?php

require_once(__DIR__ . '/../../lib/rabbitMQLib.inc');
require_once(__DIR__ . '/logging.php');

function isLoggedIn(){
    return isset($_COOKIE['Session']);
}

function getUsername(){
    $cookieArray = (array) json_decode( $_COOKIE["Session"]);
    return $cookieArray['username'];
}

function rabbitAuthClient($request){
    $client = new rabbitMQClient(__DIR__ . "/../../lib/rabbitMQ.ini","Authentication");
    $response = $client->send_request($request);
    return $response;
}
function rabbitSearchClient($request){
    $client = new rabbitMQClient(__DIR__ . "/../../lib/rabbitMQ.ini","DatabaseSearch");
    $response = $client->send_request($request);
    return $response;
}

function isSessionValid(){
    if(isset($_COOKIE["Session"])){
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
    }
    
    return false;

}

function createCart(){
    if(!isset($_COOKIE["Cart"])){
        $shoppingCart = array();
		$shoppingCart['status'] = 'empty';
		setcookie("Cart", json_encode($shoppingCart), time() + (10 * 365 * 24 * 60 * 60), "/");
    }
}

function addToCart($item){
    if(isset($_COOKIE["Cart"])){
        $temp = json_decode($_COOKIE["Cart"], true);
        if(str_contains($temp['status'], 'empty')){
            $temp['status'] = 'full';
        }
        $itemNum = count($temp);
        $temp['item'.(string) $itemNum] = $item;
        
        setcookie("Cart", json_encode($temp), time() + (10 * 365 * 24 * 60 * 60), "/");
    }
    return "Successfully added $item";
}
function clearCart(){
    if(isset($_COOKIE["Cart"])){
        $shoppingCart = array();
		$shoppingCart['status'] = 'empty';
		setcookie("Cart", json_encode($shoppingCart), time() + (10 * 365 * 24 * 60 * 60), "/");
    }
}

function getCartItems(){
    if(isset($_COOKIE["Cart"])){
        $temp = json_decode($_COOKIE["Cart"], true);
        $data = array();
        if(str_contains($temp['status'], 'empty')){
            $data['item1'] = "No items currently in your cart.";
            return $data;
        }
        for($i = 1; $i < count($temp); $i++){
            $data['item'.(string)$i] = $temp['item'.(string)$i];
        }
        
    }
    return $data;
}

function cartEmpty(){
    $temp = json_decode($_COOKIE["Cart"], true);
    if(str_contains($temp['status'], 'full')){
        return false;
    }
    return true;
}

function hasCartCookie(){
    if(!isset($_COOKIE["Cart"])){
        return false;
    }
    return true;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

?>