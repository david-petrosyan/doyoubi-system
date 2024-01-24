<?php
session_start();
if(empty($_SESSION['login_user_id'])) {
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
if (isset($_POST['introduction'])) {
  // Processing when an introduction is sent from a form
  // Update the introduction column of logged in member information
  $update_sth = $dbh->prepare("UPDATE users SET introduction = :introduction WHERE id = :id");
  $update_sth->execute([
      ':id' => $user['id'],
      ':introduction' => $_POST['introduction'],
  ]);
  // If successful, redirect to URL with query parameters indicating success
  header("HTTP/1.1 302 Found");
  header("Location: ./introduction.php?success=1");
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

<h1>  Self-introduction settings </h1>
<form method="POST">
  <textarea type="text" name="introduction" rows="5"
    ><?= htmlspecialchars($user['introduction'] ?? '') ?></textarea>
  <button type="submit">決定</button>
</form>
<?php if(!empty($_GET['success'])): ?>
<div>
  The setting process for the self-introduction text has been completed.
</div>
<?php endif; ?>
