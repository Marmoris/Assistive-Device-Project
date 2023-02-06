<?php
@include 'config.php';
session_start();

function console_log($output, $with_script_tags = true)
{
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
    if ($with_script_tags)
    {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

if(!isset($_SESSION['username'])){
   header('location: login_form.php');
}

$username = $_SESSION['username'];
$tableName = "$username" . "images";

$query = "SELECT $tableName.id, $tableName.image, $tableName.created FROM $tableName";
$result = mysqli_query($conn, $query);

function deleteFun($id){
   global $tableName, $conn;
   $deleteQuery = "DELETE from $tableName where id = $id";
   mysqli_query($conn, $deleteQuery);
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
       return $totalText;

   } catch(Exception $err) {
       header('HTTP/1.0 403 Forbidden');
       echo $err->getMessage();
   }
   return null;
}

$txt = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>user page</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="src\homeStyle.css">

</head>
<body>
   
<div class="container">

   <div class="content">
      <a href="logout.php" class="btn">logout</a>
   </div>

</div>

<div class="container2">

   <div class="container3">
      <div class="content3">
         <?php 
            if(isset($_GET["type"], $_GET["id"])){
               $type = $_GET["type"];
               $id = (int)$_GET["id"];
            
               if($type == "image"){
                  $imgQuery = "SELECT image, imgText from $tableName where id = $id";
                  $imgData = mysqli_query($conn, $imgQuery);
                  while ($row = $imgData->fetch_assoc()) {
                     echo '<img src="'.$row['image'].'"/>';
                     $txt = $row['imgText'];
                     echo '<div id="tts-player" class="control is-pulled-left is-hidden"></div><br>';
                     ?>
                     
                     <form method="post"><input type="submit" onclick="return confirm('Are you sure you want to delete this image?')" name="deleteButton" class="btn3" value="Delete" /></form>
                     <br>
                     <?php
                     if(array_key_exists('deleteButton', $_POST)) {
                        deleteFun($id);
                        echo '<meta http-equiv="refresh" content="0">';
                     }

                  }
               }
            }
         ?>

      </div>
   </div>
   <div class="container4">
      <a href="scan.php" class="btn">New Scan</a>
      <br><br>
      <?php
         while($row = mysqli_fetch_array($result)){
            echo '<a class = "imgButton" href = "user_page.php?type=image&id='.$row["id"].'">'.$row["created"].'</a><br><br>';
         }
      ?>
      
   </div>

</div>

</body>
</html>

<script>
var txt = '<?php echo"$txt"?>';
if(txt == ""){
   console.log("SAd");
}
generateTTSUrl(txt);

// Generate URL to TTS output
function generateTTSUrl(text) {
    var url = 'https://www.cereproc.com/themes/benchpress/livedemo.php';
    
    // Send request to our proxy script
    var xhr = new XMLHttpRequest();
    xhr.onload = function () {
        var response = JSON.parse(xhr.responseText);
        if (xhr.readyState == 4 && xhr.status == '200') {
                
            if (response.success === true) {
                showAudioPlayer(response.speak_url);
            } else if (response.error) {
                showErrorMessage(response.error);
            }
        } else {
            console.error(response);
        }

    };
    xhr.open('POST', 'proxy.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('service=CereProc&voice=Hannah&text=' + encodeURIComponent(text)); //HERE WE AT
    
}

// Add <audio> player to the DOM
function showAudioPlayer(ttsUrl) {
    var audioHtml = '<audio autoplay controls id="audioplayer" preload="metadata" src="' + ttsUrl + '" title="TTS Audio Clip"><p>Your browser does not support the <code>audio</code> element.</p></audio>';
    document.getElementById('tts-player').innerHTML = audioHtml;
    document.getElementById('tts-player').classList.remove('is-hidden');
}


</script>