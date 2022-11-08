<?php

require_once  '/home/ubuntu/Null/lib/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('10.10.200.1', 5672, 'webadmin', 'ChangeLater','API');
$channel = $connection->channel();
$channel->queue_declare('API', true, false, false, false);

echo "Waiting for messages \n";
function hello($msg) {
    $body = $msg->getBody();
    $payload = json_decode($body, true);
    var_dump($payload);

  //  print_r($payload[0]['strDrink']);
};

$channel->basic_consume('API', 'API', false, true, false, false, $callback);

while (true) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>