#!/usr/bin/php
<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');

  $inputedusername = "test";
	$inputedpassword = "test";

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
	$request['type'] = "login";
	$request['username'] = $inputedusername;//sending username to server
	$request['password'] = $passHash;//sending hashed password to server
	$response = $client->send_request($request);//send $request and wait to store response in $response

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";


$client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","Authentication");

//Create session information for successful login
$datetime = date('Y-m-d H:i:s', time());	//current date and time (example format: '2022-20-2022 18:46:26')
$sessionID = hash('sha256', $inputedusername . $datetime); //session hash based on username and current time

//send session information to database
$request = array();
$request['type'] = "create session";
$request['sessionID'] = $sessionID;//sending sessionID
$request['username'] = $inputedusername;//sending username
$request['datetime'] = $datetime;//send date and time	

$response = $client->send_request($request); //send $request and wait to store response in $response

if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "login info";
}

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";


echo $argv[0]." END".PHP_EOL;

