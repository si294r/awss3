<?php

require '/var/www/vendor/autoload.php';
//include "/var/www/awss3_dev.php";
include "/var/www/awss3_prod.php";
include '/var/www/mongodb_rocket.php';

use Aws\S3\S3Client;

function get_url_download($value) {
    return "http://files.parsetfss.com/e1a9cd04-e62a-4463-8524-177866bb62e6/" . $value;
}

function is_file_s3_exists($pathToFile) {
    global $clientS3;
    global $aws_bucket; // defined in include file awss3

    $filename = basename($pathToFile);

    $result = false;
    // cek if file already upload to amazon S3
    try {
        $result_head = $clientS3->headObject(array(
            'Bucket' => $aws_bucket,
            'Key' => $filename
        ));
        echo "File exists, continue next file...\r\n";
        var_dump($result_head);
        // file exists, abort upload process
        $result = true;
    } catch (Exception $ex) {
        echo "File not exists, todo: download then upload to s3...\r\n";
    }
    return $result;
}

function upload_file_s3($pathToFile) {
    global $clientS3;
    global $aws_bucket; // defined in include file awss3

    $filename = basename($pathToFile);

//    // cek if file already upload to amazon S3
//    try {
//        $result_head = $clientS3->headObject(array(
//            'Bucket' => $aws_bucket,
//            'Key' => $filename
//        ));
//        var_dump($result_head);
//        // file exists, abort upload process
//        return;
//    } catch (Exception $ex) {
//        echo "File not exists, goto upload...";
//    }
        
    reupload:
    $result = $clientS3->putObject(array(
        'Bucket' => $aws_bucket,
        'Key' => $filename,
        'SourceFile' => $pathToFile
    ));
    var_dump($result);

    $clientS3->waitUntil('ObjectExists', array(
        'Bucket' => $aws_bucket,
        'Key' => $filename
    ));

    $reupload = false;
    try {
        $result_head = $clientS3->headObject(array(
            'Bucket' => $aws_bucket,
            'Key' => $filename
        ));
        var_dump($result_head);
    } catch (Exception $ex) {
        echo "Error while get headObject... ";
        $reupload = true;
    }

    if ($reupload) {
        goto reupload;
    }
}

$start_time = (new DateTime(gmdate('Y-m-d H:i:s')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s');

$connection_string = "mongodb://"
        . $mongo_username . ":"
        . $mongo_password . "@"
        . $mongo_host . "/"
        . $mongo_database;

$client = new MongoDB\Client($connection_string, $mongo_options); // create object client 

$clientS3 = S3Client::factory(array(
            'credentials' => array(
                'key' => $aws_key,
                'secret' => $aws_secret_key
            )
        ));

$db = $client->$mongo_database; // select database
//$db->setSlaveOkay();

echo "Query Data cloud...";
$documents = $db->selectCollection('_User')->find([
    'cloudSaveData' => ['$exists' => TRUE]//,
//    'facebookID' => '1113271782022703'
//    'cloudSaveDataAndroid' => 'tfss-e9f0c049-ded3-4187-b459-b5e4d84766f9-cloudSaveDataAndroid'
    ]);
echo "Done\r\n";

$arr_doc = [];
foreach ($documents as $document) {
    $arr_doc[] = $document;
//    break;
}
echo  "Total Documents: ".count($arr_doc)."\r\n";

$total_count = count($arr_doc);
$goto_counter = 0;
foreach ($arr_doc as $no_doc=>$document) {
    echo "Processing No ".($no_doc+1)." from ".$total_count."\r\n";
    $file_cloudSaveData = $document["cloudSaveData"];

    if (is_file_s3_exists($file_cloudSaveData)) continue;
    
    $url_download = get_url_download($file_cloudSaveData);

    $goto_counter = 0;
    
    redownload:
    exec("wget " . $url_download);

    if (!is_file($file_cloudSaveData)) {
        $goto_counter++;
        if ($goto_counter > 10) {
            file_put_contents("error_download_".$document['facebookID'].".log", ($no_doc+1) .": ". $url_download);
            continue;
        } else {
            sleep(3);
            goto redownload;
        }
    }

    echo "Uploading " . $file_cloudSaveData . "...\r\n";
    upload_file_s3($file_cloudSaveData);

    echo "Remove download file " . $file_cloudSaveData . "\r\n";
    unlink($file_cloudSaveData);
    //break;
}

echo  "Total Documents: ".count($arr_doc)."\r\n";

$memory_usage = memory_get_usage(true);
$end_time = (new DateTime(gmdate('Y-m-d H:i:s')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s');

$content  = "Start Time = ".$start_time."\r\n";
$content .= "End Time = ".$end_time."\r\n";
$content .= "Memory Usage = ".$memory_usage."\r\n";
$content .= "Total Documents = ".count($arr_doc)."\r\n";

file_put_contents("awss3.log", $content);