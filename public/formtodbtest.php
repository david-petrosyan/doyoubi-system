<?php
$dbh = new PDO ('mysql:host=mysql;dbname=techc','root','');

if (isset($_POST['body'])) {
  // If there is a form parameter body sent by POST

  // insert
  $insert_sth =$dbh ->prepare ("INSERT INTO hogehoge (text) VALUES (:body)");
  $insert_sth ->execute([
      ':body'=>$_POST['body'],
  ]);

  // redirect when done
  // If you don't redirect, you'll post again with the same content when reloading
  header("HTTP/1.1 302 Found");
  header("Location: ./formtodbtest.php");
  return;
}

?>

<!-- Post the form to this file itself -->
<form method="POST" action="./formtodbtest.php">
  <textarea name="body"></textarea>
  <button type ="submit">submit</button>
</form>
