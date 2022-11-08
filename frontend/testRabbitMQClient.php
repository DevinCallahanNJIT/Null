#!/usr/bin/php
<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');

	$searchString = "ma";
	$client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","APISearch");
	$request = $searchString;
	$client->publish($request);

?>
