<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

session_start();
if (empty( $_SESSION [ 'login_user_id' ])) { // Not available if not logged in
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

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
      ':user_id' => $_SESSION [ 'login_user_id' ], // Primary key of logged in member information
      ':body' => $_POST [ 'body' ], // Post body sent from the form
      ':image_filename' => $image_filename , // Name of saved image (can be null)
  ]);

  // Redirect when processing is complete
  // If you don't redirect, you will POST with the same content again when reloading
  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}

// Get post data. In order to use placeholders in the IN clause, as many ``?'' characters as there are elements in $target_user_ids are added.
$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries'
  . ' INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' WHERE'
  . '   bbs_entries.user_id IN'
  . '     (SELECT followee_user_id FROM user_relationships WHERE follower_user_id = :login_user_id)'
  . '   OR bbs_entries.user_id = :login_user_id'
  . ' ORDER BY bbs_entries.created_at DESC';
$select_sth = $dbh->prepare($sql);
$select_sth->execute([
    ':login_user_id' => $_SESSION['login_user_id'],
]);

// Prepare a function to output the body HTML
function bodyFilter (string $body): string
{
    $body = htmlspecialchars($body); // Escape processing
    $body = nl2br( $body); // Convert newline character to <br> element

    // Make a string such as >>1 an in-page link to the post with the corresponding number (response anchor function)
    // Be careful as ">" (half-width greater-than symbol) is escaped with htmlspecialchars()
    $body = preg_replace('/&gt;&gt;(\d+)/', '<a href="#entry$1">&gt;&gt;$1</a>', $body);

    return $body;
}
?>

<?php if(empty($_SESSION['login_user_id'])): ?>
You must be <a href ="/login.php"> login </a>   to post .
<?php else: ?>
  Currently logged in ( <a href ="/setting/index.php"> Click here for the settings screen </a>)
  <!-- POST the form to this file itself -->
  <form method ="POST" action = "./bbs.php"> <!-- Remove enctype -->
    <textarea name="body" required></textarea>
    <div style="margin: 1em 0;">
      <input type="file" accept="image/*" name="image" id="imageInput">
    </div>
    <input id ="imageBase64Input" type ="hidden" name ="image_base64"> <!-- input for sending base64 (hidden) -->
    <canvas id ="imageCanvas" style ="display: none;"> </canvas> <!-- Canvas used for image reduction (hidden) -->
    <button type ="submit"> Submit </button>
  </form>
<?php endif; ?>
<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt id="entry<?= htmlspecialchars($entry['id']) ?>">
      number
    </dt>
    <dd>
      <?= htmlspecialchars($entry['id']) ?>
    </dd>
    <dt>
      Contributor
    </dt>
    <dd>
      <a href="/profile.php?user_id=<?= $entry['user_id'] ?>">
        <?php if (!empty( $entry [ 'user_icon_filename' ])): // Show icon image if there is one ?>
        <img src="/image/<?= $entry['user_icon_filename'] ?>"
          style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
        <?php endif; ?>

        <?= htmlspecialchars($entry['user_name']) ?>
        (ID: <?= htmlspecialchars($entry['user_id']) ?>)
      </a>
    </dd>
    <dt> Date and time </dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt> Content </dt>
    <dd>
      <?= bodyFilter($entry['body']) ?>
      <?php if(!empty($entry['image_filename'])): ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {
      // If not selected
      return;
    }

    const file = imageInput.files[0];
    if (!file.type.startsWith ('image/')) {  // Skip if not an image
      return;
    }

    // Image reduction processing
    const imageBase64Input = document.getElementById ("imageBase64Input");  // input to send base64
    const canvas = document.getElementById("imageCanvas");  // canvas to draw
    const reader = new FileReader();
    const image = new Image();
    reader.onload = () =>  {  // Specify the process to run when the file has finished loading
      image.onload = ()=> {  // Specify the process to run when loading as an image is completed

        //Determine the size to be reduced while maintaining the original aspect ratio and specify it as the canvas's height and width
        const originalWidth = image.naturalWidth;  // Original image width
        const originalHeight = image.naturalHeight;  // Original image height
        const maxLength = 1000;  // Width and height shall be reduced to 1000 or less
        if ( originalWidth <= maxLength && originalHeight <= maxLength)  {  // Leave as is if both are less than maxLength
            canvas.width = originalWidth;
            canvas.height = originalHeight;
        } else if ( originalWidth  >  originalHeight )  {  // For horizontal images
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
