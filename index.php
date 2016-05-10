<?php

require '/var/www/vendor/autoload.php';
include "/var/www/awss3_dev.php";

use Aws\S3\S3Client;

// Instantiate the S3 client with your AWS credentials
$client = S3Client::factory(array(
    'credentials' => array(
        'key'    => $aws_key,
        'secret' => $aws_secret_key
    )
));

//var_dump($client);
$result = $client->listBuckets();
var_dump($result);

die;

// Upload an object by streaming the contents of a file
// $pathToFile should be absolute path to a file on disk
$result = $client->putObject(array(
    'Bucket'     => $bucket,
    'Key'        => 'data_from_file.txt',
    'SourceFile' => $pathToFile,
    'Metadata'   => array(
        'Foo' => 'abc',
        'Baz' => '123'
    )
));

// We can poll the object until it is accessible
$client->waitUntil('ObjectExists', array(
    'Bucket' => $this->bucket,
    'Key'    => 'data_from_file.txt'
));