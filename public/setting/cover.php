<?php
session_start();

if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// connect to DB
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
// Get the logged in member information from the login ID in the session
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

if (isset($_POST['image_base64'])) {
  // If there is a form parameter image_base64 sent via POST

  $image_filename = null;
  if (!empty($_POST['image_base64'])) {
    // Delete the first data:~base64,
    $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);

    // Decode from base64 to binary
    $image_binary = base64_decode($base64);

    // Decide on a new file name and output the binary
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
    $filepath =  '/var/www/upload/image/' . $image_filename;
    file_put_contents($filepath, $image_binary);
  }

  // Update the cover image file name of logged in member information
  $update_sth = $dbh->prepare("UPDATE users SET cover_filename = :cover_filename WHERE id = :id");
  $update_sth->execute([
      ':id' => $user['id'],
      ':cover_filename' => $image_filename,
  ]);

  // Redirect when processing is complete
  // If you don't redirect, you will POST with the same content again when reloading
  header("HTTP/1.1 302 Found");
  header("Location: ./cover.php");
  return;
}

?>








<html>
<style>

body{
 background: linear-gradient(#e66465, #9198e5);
}

h1 {
 text-align: center;
 margin: 20px;
 width: 400px;
 height: 40px;
 background: ;
 color: #bbb;
 font-size: 1.8em;
 border-radius: ;
}

form{
 width: 40%;
 text-align: center;
 background: rgba(255,255,255,0.5);
 color: white;
 font-size: 1.8em;
 border-top: grey 4px solid;
 border-bottom: grey 4px solid;
 border-radius: 4px;
}

button{
 margin: 20px;
 text-align: center;
 background: green;
 color: white;
 font-size: 1.2em;
 border: grey 2px solid;
 border-radius: 20px;
}

button:hover {
 color: grey;
 cursor: pointer;
}

p {
 color: ;
 font-size: 2em;


}


a {
 text-decoration: none;
}

</style>
</html>









<a href ="./index.php "> Return to settings list </a>

<h1> Cover image </h1>

<div>
  <?php if(empty($user['cover_filename'])): ?>
  Not currently set
  <?php  else : ?>
  <img src="/image/<?= $user['cover_filename'] ?>"
    style="height: 5em; width: 10em; object-fit: cover;">
  <?php endif; ?>
</div>

<!-- POST the form to this file itself -->
<form method="POST" action="./cover.php">
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <input  id =" imageBase64Input " type =" hidden " name =" image_base64 "> <!-- input for sending base64 (hidden) -->
  <canvas  id =" imageCanvas " style =" display: none; " > </canvas> <!-- Canvas used for image reduction (hidden) -->
  <button  type =" submit "> Upload </button>
</form>

<hr>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {
      // If not selected
      return;
    }

    const file = imageInput.files[0];
    if  ( ! file . type . startsWith ( 'image/' ) ) {  // Skip if not an image
      return;
    }

    // Image reduction processing
    const  imageBase64Input  =  document . getElementById ( "imageBase64Input" ) ;  // input to send base64
    const canvas = document . getElementById ( "imageCanvas" ) ;  // canvas to draw
    const reader = new FileReader();
    const image = new Image ( ) ;
    reader . onload  =  ( )  =>  {  // Specify the process to run when the file has finished loading
      image . onload  =  ( )  =>  {  // Specify the process to run when loading as an image is completed

        //Determine the size to be reduced while maintaining the original aspect ratio and specify it as the canvas's height and width
        const originalWidth  =  image . naturalWidth ;  // Original image width
        const originalHeight  =  image . naturalHeight ;  // Original image height
        const maxLength  =  1000 ;  // Width and height shall be reduced to 1000 or less
        if ( originalWidth  <=  maxLength  &&  originalHeight  <=  maxLength )  {  // Leave as is if both are less than maxLength
            canvas.width = originalWidth;
            canvas.height = originalHeight;
        } else if  ( originalWidth  >  originalHeight )  {  // For horizontal images
            canvas.width = maxLength;
            canvas.height = maxLength * originalHeight / originalWidth;
        } else {  // For portrait images
            canvas.width = maxLength * originalWidth / originalHeight;
            canvas.height = maxLength;
        }

        // Actually draw the image on the canvas (it's hard to see because the canvas is hidden by display:none, but...)
        const context = canvas.getContext("2d");
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        // Convert the canvas content to base64 and set it to the input value
        imageBase64Input.value = canvas.toDataURL();
      } ;
      image.src = reader.result;
    } ;
    reader.readAsDataURL(file);
  } ) ;
} ) ;
</script>
