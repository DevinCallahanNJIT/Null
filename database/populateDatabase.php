<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');

function requestProcessor($request)
{
	echo "\nReceived Data".PHP_EOL;
	
	//var_dump($request);

	print_r(count($request));
}

$server = new rabbitMQServer("../lib/rabbitMQ.ini","API");

echo "API Fetcher BEGIN \n".PHP_EOL;
$server->process_requests('requestProcessor');
echo "API Fetcher END \n".PHP_EOL;
exit();




?>