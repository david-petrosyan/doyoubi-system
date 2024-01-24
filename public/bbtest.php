<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if(isset($_POST['body'])) {
  // If there is a form parameter body sent by POST

  // insert
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body) VALUES (:body)");
  $insert_sth->execute([
      ':body' => $_POST['body'],
  ]);

  // redirect when done
  // If you don't redirect, you'll post again with the same content when reloading
  header("HTTP/1.1 302 Found");
  header("Location: ./bbstest.php");
  return;
}

// Get what we have saved so far
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>

<!-- Post the form to this file itself -->
<form method="POST" action="./bbstest.php">
  <textarea name="body"></textarea>
  <button type ="submit">submit</button>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>ID</dt>
    <dd><?=$entry['id']?></dd>
    <dt> DateTime </dt> _ _ _ _
    <dd><?= $entry['created_at'] ?></dd>
    <dt> content </dt> _ _ _ _
    <dd> <?=nl2br(htmlspecialchars($entry['body'])) // must use htmlspecialchars() ?> </dd>
  </dl>
<?php endforeach?>
