<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

session_start();
if(empty($_SESSION [ 'login_user_id' ])) { // Not available if not logged in
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}
// Get current login information
$user_select_sth = $dbh->prepare("SELECT * from users WHERE id = :id");
$user_select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $user_select_sth->fetch();

// Post processing
if (isset($_POST['body']) && !empty($_SESSION['login_user_id'])) {

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

  // insert
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename)");
  $insert_sth->execute([
      ':user_id' => $_SESSION ['login_user_id'], // Primary key of logged in member information
      ':body' => $_POST ['body'], // Post body sent from the form
      ':image_filename' => $image_filename , // Name of saved image (can be null)
  ]);

  // Redirect when processing is complete
  // If you don't redirect, you will POST with the same content again when reloading
  header("HTTP/1.1 302 Found");
  header("Location: ./timeline.php");
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
 margin: auto;
 width: 400px;
 height: 40px;
 background: ;
 color: #bbb;
 font-size: 1.8em;
 border-radius: ;
}
hr {
 margin-top: 40px;
 margin-bottom: 40px;
}
form{
 padding: 20px;
 width: 20%;
 height: 10%;
 margin: auto;
 margin-bottom: 20px;
 text-align: center;
 background: rgba(255,255,255,0.5);
 color: white;
 font-size: 1.8em;
 border-top: grey 4px solid;
 border-bottom: ;
 border-radius: 4px;
}
dl{
 margin-top: 20px;
}
button{
 margin-top: ;
 text-align: center;
 background: green;
 color: white;
 font-size: 0.8em;
 border: grey 1px solid;
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
.p {
 color white;
 font-size: 20px;
 width: 20%;
 margin: auto;
}
.p span {
 color: white;
}
@media only screen and (min-width: 800px) {
 form {
  
}
</style>
</html>




<div class="p">
  Currently logged in as<span> <?= htmlspecialchars($user['name']) ?> (ID: <?= $user ['id'] ?>)</span>
</div>
<div style="margin-bottom: 1em;">
  <a href ="/setting/index.php"> Settings screen </a>
  /
  <a href ="/users.php"> Member list screen </a>
</div>

<!-- POST the form to this file itself -->
<form method ="POST" action ="./timeline.php"> <!-- Remove enctype -->
  <textarea name="body" required></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" multiple="true" id="imageInput">
  </div>
  <input id ="imageBase64Input" type ="hidden" name ="image_base64"> <!-- input for sending base64 (hidden) -->
  <canvas id ="imageCanvas" style ="display: none;"> </canvas> <!-- Canvas used for image reduction (hidden) -->
  <button type ="submit"> Submit </button>
</form>

<hr>

<dl id="entryTemplate" style="display: none; margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
  <dt> Number </dt> _ _ _ _
  <dd data-role="entryIdArea"></dd>
  <dt> Posted by </dt>
  <dd>
      <a href="" data-role="entryUserAnchor">
      <img data-role="entryUserIconImage"
        style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <span data-role="entryUserNameArea"></span>
    </a>
  </dd>
  <dt> Date and time </dt>
  <dd data-role = "entryCreatedAtArea"> </dd>
  <dt> Content </dt>
  <dd data-role="entryBodyArea">
  </dd>
</dl>
<div id="entriesRenderArea"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {

  const entryTemplate = document.getElementById('entryTemplate');
  const entriesRenderArea = document.getElementById('entriesRenderArea');

  const request = new XMLHttpRequest();
  request.onload = (event) => {
    const response = event.target.response;
    response.entries.forEach((entry) => {
      // Copy elements from template
      const entryCopied = entryTemplate.cloneNode(true);

      // Rewrite display: none to display: block
      entryCopied.style.display = 'block';

      // Set the id attribute (for response anchor)
      entryCopied.id = 'entry' + entry.id.toString();

      // Display number (ID)
      entryCopied.querySelector('[data-role="entryIdArea"]').innerText = entry.id.toString();
      // If the icon image exists, display it, otherwise hide the img element
      if (entry.user_icon_file_url !== undefined) {
        entryCopied.querySelector('[data-role="entryUserIconImage"]').src = entry.user_icon_file_url;
      } else {
        entryCopied.querySelector('[data-role="entryUserIconImage"]').display = 'none';
      }

      // display name
      entryCopied.querySelector('[data-role="entryUserNameArea"]').innerText = entry.user_name;

      // Set the link destination (profile) URL in the name
      entryCopied.querySelector('[data-role="entryUserAnchor"]').href = entry.user_profile_url;

      // Display posting date and time
      entryCopied.querySelector('[data-role="entryCreatedAtArea"]').innerText = entry.created_at;

      // Display the main text (this is HTML, so use innerHTML)
      entryCopied.querySelector('[data-role="entryBodyArea"]').innerHTML = entry.body;

            // Display image at the bottom of the body if an image exists
      if (entry.image_file_url !== undefined) {
        const imageElement = new Image() ;
        imageElement.src = entry.image_file_url ;  // Set image URL
        imageElement.style.display = 'block' ;  // Make it a block element (img element is an inline element by default)
        imageElement.style.marginTop = '1em';  // Set the margin at the top of the image
        imageElement.style.maxHeight = '300px';  // Set the maximum size (height) to display the image
        imageElement.style.maxWidth = '300px';  // Set the maximum size (horizontal) to display the image
        entryCopied.querySelector ('[data-role="entryBodyArea"]') .appendChild ( imageElement ) ;  // Add image to body area
      }


      // Finally do the actual drawing
      entriesRenderArea.appendChild(entryCopied);
    });
  }
  request.open('GET' , '/timeline_json.php' , true);  // Hit timeline_json.php
  request.responseType = 'json';
  request.send();


  // For reducing the image below

  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {
      // If not selected
      return;
    }

    const file = imageInput.files[0];
    if (!file.type.startsWith('image/')) {  // Skip if not an image
      return;
    }

    // Image reduction processing
    const imageBase64Input = document.getElementById("imageBase64Input");  // input to send base64
    const canvas = document.getElementById("imageCanvas");  // canvas to draw
    const reader = new FileReader();
    const image = new Image();
    reader.onload = () => {  // Specify the process to run when the file has finished loading
      image.onload = () => {  // Specify the process to run when loading as an image is completed

        //Determine the size to be reduced while maintaining the original aspect ratio and specify it as the canvas's height and width
        const originalWidth = image.naturalWidth;  // Original image width
        const originalHeight = image.naturalHeight;  // Original image height
        const maxLength = 1000;  // Width and height shall be reduced to 1000 or less
        if (originalWidth <= maxLength && originalHeight <= maxLength)  {  // Leave as is if both are less than maxLength
            canvas.width = originalWidth;
            canvas.height = originalHeight;
        } else if ( originalWidth > originalHeight )  {  // For horizontal images
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
      };
      image.src = reader.result;
    };
    reader.readAsDataURL(file);
  });
});
</script>
