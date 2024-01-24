<?php

if(isset($_POST['body'])) {
  // If there is a form parameter body sent by POST

  print('I received the following!<br>');

  // Always escape using htmlspecialchars() when outputting sent content
  // Anti-XSS.
  print(nl2br(htmlspecialchars($_POST['body'])));
}

?>

<!-- Post the form to this file itself -->
<form method ="POST" action=" ./formtesp.php">
  <textarea name = "body"></textarea>
  <button type ="submit">submit</button>
</form>
