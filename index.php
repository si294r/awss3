<?php

require '/var/www/vendor/autoload.php';
include "/var/www/awss3_dev.php";
include '/var/www/mongodb_rocket.php';

use Aws\S3\S3Client;

$connection_string = "mongodb://"
        . $mongo_username . ":"
        . $mongo_password . "@"
        . $mongo_host . "/"
        . $mongo_database;

$client = new MongoDB\Client($connection_string, $mongo_options); // create object client 

$db = $client->$mongo_database; // select database
$db->setSlaveOkay();

$document = $db->selectCollection('_User')->findOne(['cloudSaveDataAndroid' => [ '$exists' => TRUE ]]);

var_dump($document);
die;

$clientS3 = S3Client::factory(array(
            'credentials' => array(
                'key' => $aws_key,
                'secret' => $aws_secret_key
            )
        ));


function upload_file_s3($pathToFile) {
    global $clientS3;
    global $aws_bucket; // defined in include file awss3
    
    $filename = basename($pathToFile);

    $result = $clientS3->putObject(array(
        'Bucket' => $aws_bucket,
        'Key' => $filename,
        'SourceFile' => $pathToFile,
        'Metadata' => array(
        )
    ));

    $clientS3->waitUntil('ObjectExists', array(
        'Bucket' => $aws_bucket,
        'Key' => $filename
    ));
}
