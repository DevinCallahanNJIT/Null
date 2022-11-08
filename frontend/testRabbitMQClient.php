#!/usr/bin/php
<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');

/*
	Nothing In This File Is Relevant Towards The System.
	This Code Exists Just To Test Parts Of Functions Being Developed Independently, And Is Changed Frequently.
	Code In This File May Exist As Incomplete Code And Should Not Be Referenced For Other Code.
*/


	$client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","DatabaseSearch");
	$request = array();
/*
	$request['type']='createRecipe';
	$request['cocktailName']='Mimosa';
	$request['username']='test';
	$request['instructions']='Ensure both ingredients are well chilled, then mix into glass. Serve cold.';
	$request['imageRef']='https://www.thecocktaildb.com/images/media/drink/juhcuu1504370685.jpg';
	$request['ingredient1']='Champagne';
	$request['measurement1']='Chilled';
	$request['ingredient2']='Orange Juice';
	$request['measurement2']='2 oz';
*/
	$request['type'] = 'searchRecipe';
	$request['ingredientName'] = 'Milk';
	
	$response = $client->send_request($request);//send $request and wait to store response in $response

	echo "client received response: ".PHP_EOL;
	print_r($response);
	echo "\n\n";


echo $argv[0]." END".PHP_EOL;

