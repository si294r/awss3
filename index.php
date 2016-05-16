<?php

require '/var/www/vendor/autoload.php';
include "/var/www/awss3_dev.php";
include '/var/www/mongodb_rocket.php';

use Aws\S3\S3Client;

function get_url_download($value) {
    return "http://files.parsetfss.com/e1a9cd04-e62a-4463-8524-177866bb62e6/" . $value;
}

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

$connection_string = "mongodb://"
        . $mongo_username . ":"
        . $mongo_password . "@"
        . $mongo_host . "/"
        . $mongo_database;

$client = new MongoDB\Client($connection_string, $mongo_options); // create object client 

$db = $client->$mongo_database; // select database
//$db->setSlaveOkay();

$document = $db->selectCollection('_User')->findOne(['cloudSaveDataAndroid' => [ '$exists' => TRUE ]]);

$url_download = get_url_download($document["cloudSaveDataAndroid"]);
//var_dump($url_download);
//die;

exec("wget ".$url_download);

$clientS3 = S3Client::factory(array(
            'credentials' => array(
                'key' => $aws_key,
                'secret' => $aws_secret_key
            )
        ));

upload_file_s3($document["cloudSaveDataAndroid"]);

