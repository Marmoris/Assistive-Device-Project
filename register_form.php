<?php

@include 'config.php';

if(isset($_POST['submit'])){
   $username = mysqli_real_escape_string($conn, $_POST['username']);
   $pass = md5($_POST['password']);
   $cpass = md5($_POST['cpassword']);

   $select = " SELECT * FROM user_form WHERE username = '$username' && password = '$pass' ";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){

      $error[] = 'User already exists!';

   }else{
      if($pass != $cpass){
         $error[] = 'password not matched!';
      }else{
         $insert = "INSERT INTO user_form(username, password) VALUES('$username','$pass')";
         mysqli_query($conn, $insert);
         
         $tableName = "$username" . "images";
         
         $imgTable = "CREATE TABLE $tableName(id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, image longblob NOT NULL, created datetime NOT NULL DEFAULT current_timestamp(), imgText longblob NOT NULL)";
         
         if ($conn->query($imgTable) === TRUE) {
            echo "Table MyGuests created successfully";
          } else {
            echo "Error creating table: " . $conn->error;
          }

         header('location:login_form.php');
      }
   }
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register form</title>
   <!-- custom css file link  -->
   <link rel="stylesheet" href="src\style.css">
   

</head>
<body>
   
<div class="form-container">

   <form action="" method="post">
      <h3>register now</h3>
      <?php
      if(isset($error)){
         foreach($error as $error){
            echo '<span class="error-msg">'.$error.'</span>';
         };
      };
      ?>
      
      <input type="username" name="username" required placeholder="enter your username">
      <input type="password" name="password" required placeholder="enter your password">
      <input type="password" name="cpassword" required placeholder="confirm your password">
      <input type="submit" name="submit" value="register now" class="form-btn">
      <a href="login_form.php" class = "btn">login now</a>
   </form>

</div>

</body>
</html>