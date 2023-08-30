<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (isset($_POST['body'])) {
  // If there is a form parameter body sent by POST

  $image_filename = null;
  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
    // if there is an image uploaded
    if (preg_match('/^image\//', $_FILES['image']['type']) !== 1) {
    if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
      // if what was uploaded was not an image
      header("HTTP/1.1 302 Found");
      header("Location: ./bbsimagetest.php");
    }

    // get the extension from the original filename
    $pathinfo = pathinfo($_FILES['image']['name']);
    $extension = $pathinfo['extension'];
    // Determine new file name. Determined by time + random number so as not to overlap with image files of other posts.
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
    $filepath =  '/var/www/upload/image/' . $image_filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }

  // insert
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body, image_filename) VALUES (:body, :image_filename)");
  $insert_sth->execute([
    ':body' => $_POST['body'],
    ':image_filename' => $image_filename,
  ]);

  // redirect when done
  // If you don't redirect, you'll post again with the same content when reloading
  header("HTTP/1.1 302 Found");
  header("Location: ./bbsimagetest.php");
  return;
}

// Get what we have saved so far
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>
<link rel="stylesheet" href="looks.css">
<head><title>No Time To Edit</title></head>

<!-- Post the form to this file itself -->
<form method="POST" action="./bbsimagetest.php" enctype="multipart/form-data">
<div class="abc">
<h1>Board Header</h1>
  <textarea name="body"></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <button  type ="submit"> submit </button>
</div>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>ID</dt>
    <dd><?= $entry['id'] ?></dd>
    <dt> DateTime </dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt> content </dt>
    <dd>
      <?= nl2br(htmlspecialchars($entry [ 'body' ])) // Always use htmlspecialchars() ?>
      <?php  if (!empty($entry [ 'image_filename' ])): // If there is an image, display it using the img element ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif;?>
    </dd>
  </dl>
<?php endforeach?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {
      // 未選択の場合
      return;
    }
    if (imageInput.files[0].size > 5 * 1024 * 1024) {
      // ファイルが5MBより多い場合
      alert("5MB以下のファイルを選択してください。");
      imageInput.value = "";
    }
  });
});
</script>
