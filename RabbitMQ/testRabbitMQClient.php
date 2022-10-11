#!/usr/bin/php
<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');

$client = new rabbitMQClient("../lib/RabbitMQ.ini","Authentication");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "register";
$request['username'] = "newUsername";
$request['password'] = "newPassword";
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);
//
echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
