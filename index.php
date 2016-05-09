<?php

use Aws\S3\S3Client;

// Instantiate the S3 client with your AWS credentials
$client = S3Client::factory(array(
    'credentials' => array(
        'key'    => 'AKIAJKL333NF43K2QMNQ',
        'secret' => '18wGr0qFrOpg3XClNyT2UK9zk4UfL0EUEXNAXvH3',
    )
));

$result = $client->listBuckets();
print_r($result);