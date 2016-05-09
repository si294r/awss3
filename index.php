<?php

use Aws\S3\S3Client;

include "/var/www/awss3_dev.php";

// Instantiate the S3 client with your AWS credentials
$client = S3Client::factory(array(
    'credentials' => array(
        'key'    => $aws_key,
        'secret' => $aws_secret_key,
    )
));

$result = $client->listBuckets();
print_r($result);