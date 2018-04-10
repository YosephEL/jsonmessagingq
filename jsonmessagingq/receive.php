<?php

/*
 * this demo version program can able to receive the message from the rabbitmq messaging queue
 * and store into a mysql database
 */
// include the library

require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
//create a connection to the server

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

//publish a message to the task_queue:
$channel->queue_declare('task_queue', false, true, false, false);

echo ' [*] Waiting for the messages', "\n";

// callback array function for message queue
$callback = function($msg){
    echo " [x] Received ", $msg->body, "\n";
    // decode the jeson message
    var_dump(json_decode($msg->body, true));
    echo " [x] Done", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

    $array_value =  json_decode($msg->body, true);

    $array_values = array_values($array_value);

    // get the array value from the jeson message

    //Declare the db
    $servername = "localhost";
    $username = "root";
   $password = "magentodbpass";
   $dbname = "lsapp";
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// insert into the database pr_producers table the array values

// Insertion code for json object 
        
//    $sql = "INSERT INTO `pr_producers` (`PK_pr_producers`, `FK_pr_regions`, `firstname`, `lastname`, `family_members`,`lng`,  `lat`, `adress`, `phone_number`, `location`, `statement`, `id`)
// VALUES('".$array_to_string['infogebana_togo'].$array_to_string['pr_producer'].[0].$array_to_string.['PK_pr_producers'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['FK_pr_regions'] . "', '" .$array_to_string['infogebana_togo']['pr_producer'][0]['firstname'] . "', '" .$array_to_string['infogebana_togo']['pr_producer'][0]['lastname'] . "', '" .$array_to_string['infogebana_togo']['pr_producer'][0]['lang'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['lat'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['family_member'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['address'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['phone_number'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['location'] . "','" .$array_to_string['infogebana_togo']['pr_producer'][0]['statement'] . "',NULL);";
        
        

        $sql = "INSERT INTO `pr_producers` (`PK_pr_producers`, `FK_pr_regions`, `firstname`, `lastname`, `family_members`,`lng`,  `lat`, `adress`, `phone_number`, `location`, `statement`, `id`)
 VALUES('$array_values[0]','$array_values[1]', '$array_values[2]', '$array_values[3]', '$array_values[4]', '$array_values[5]', '$array_values[6]','$array_values[7]','$array_values[8]','$array_values[9]','$array_values[10]',NULL);";


        $conn->exec($sql);
        echo "New record created successfully";
    }
    catch(PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;

};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}
//we close the channel and the connection

$channel->close();
$connection->close();

