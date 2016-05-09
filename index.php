<?php

require '/var/www/vendor/autoload.php';
include "/var/www/awss3_dev.php";

use Aws\S3\S3Client;

// Instantiate the S3 client with your AWS credentials
$client = S3Client::factory(array(
    'credentials' => array(
        'key'    => $aws_key,
        'secret' => $aws_secret_key,
    )
));

$result = $client->listBuckets();
print_r($result);