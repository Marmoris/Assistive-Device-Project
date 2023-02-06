<?php

session_start();
$image_parts = $_POST['image']; //base 64 image

if(isset($image_parts)){
    $dbHost     = 'localhost';
    $dbUsername = 'root';
    $dbPassword = '';
    $dbName     = 'user_db';

    $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if($db->connect_error){
        die("Connection failed: " . $db->connect_error);
    }

    $dataTime = date("Y-m-d H:i:s");

    $username = $_SESSION['username'];
    $tableName = "$username" . "images";

    base64_to_jpeg($image_parts, "img.jpg");
    $text = uploadToApi("img.jpg");

    $insert = $db->query("INSERT into $tableName (image, created, imgText) VALUES ('$image_parts', '$dataTime', '$text')");
    
}

function base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' ); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

    // clean up the file resource
    fclose( $ifp ); 

    return $output_file; 
}

function uploadToApi($target_file){
    require __DIR__ . '/vendor/autoload.php';
    $fileData = fopen($target_file, 'r');
    $client = new \GuzzleHttp\Client();
    try {
        $r = $client->request('POST', 'https://api.ocr.space/parse/image',[
            'headers' => ['apiKey' => 'helloworld'],
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $fileData
                ]
            ]
        ], ['file' => $fileData]);
        $response =  json_decode($r->getBody(),true); //the retrieved text
        
        $totalText = "";
        foreach($response['ParsedResults'] as $pareValue) { //looks through all elements of response to return a valid string. 
            $totalText = $totalText.$pareValue['ParsedText'];
        }
        $totalText = trim(preg_replace('/\s+/', ' ', $totalText));
        return $totalText;

    } catch(Exception $err) {
        header('HTTP/1.0 403 Forbidden');
        echo $err->getMessage();
    }
    return null;
}



?>