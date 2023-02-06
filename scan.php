<?php
@include 'config.php';
session_start();

if(!isset($_SESSION['username'])){
   header('location: login_form.php');
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" ></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.24/webcam.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <title>Camera Test</title>

    <link rel="stylesheet" href="src\homeStyle.css">
</head>
<body>
<div class="container">

<div class="content">
<a href="user_page.php" class="btn2">home</a>
<a href="logout.php" class="btn2">logout</a>
</div>

</div>

    <div class = "scanContainer">
        <br>

        <div id="my_camera" class="pre_capture_frame" ></div>
        <input type="hidden" name="captured_image_data" id="captured_image_data">
        <br>

        <input type="button" class = "scanButton" value="Take Snapshot" onClick="take_snapshot()">
        <br>
        <br>

        <div id="results" >
            <img style="width: 350px;" class="after_capture_frame" src="image_placeholder.jpg" />
        </div>
        <br>

        <button type="button" class="scanButton" onclick="saveSnap()">Save Picture</button>
        <br>
        <br>
    </div> 

</body>

<script language="JavaScript">
    // Configure a few settings and attach camera 250x187
    Webcam.set({
     width: 500,
     height: 375,
     image_format: 'jpeg',
     jpeg_quality: 90
    });	 
    Webcam.attach( '#my_camera' );
   
   function take_snapshot() {
    // take snapshot and get image data
    Webcam.snap( function(data_uri) {
    // display results in page
    document.getElementById('results').innerHTML = 
     '<img class="after_capture_frame" src="'+data_uri+'"/>';
    $("#captured_image_data").val(data_uri);
    });	 
   }

   function saveSnap(){
    var base64data = $("#captured_image_data").val();
    if(base64data != ""){
        $.ajax({
			type: "POST",
			url: "capture_image_upload.php",
			data: {image: base64data},
		});
    }
   }
   

</script>

</html>