<?php
// Get session ID (if not, create and set a new one
session_start();

// If the session does not have a login ID (= if you are not logged in) redirect to the login screen
if(empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./login.php");
  return;
}


// connect to DB
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
// Get the logged in member information from the login ID in the session
$insert_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$insert_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $insert_sth->fetch();
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
 text-align: center;
 color: ;
 font-size: 2em;
 width: 40%;
 margin: auto;
}
dl {
 width: 40%;
 margin: auto;
 font-size: 1.6em;
 color: white;
 border-bottom: grey 2px solid;
}

a {
 text-decoration: none;
}

</style>
</html>



<h1> Login completed </h1>

<p>
  DONE! <br>
<a href="/timeline.php">Click here for timeline</a>
</p>
<hr>
<p>
  In addition, the member information for which you are currently logged in is as follows.
</p>
<dl>  <!-- When outputting registration information, be sure to use htmlspecialchars() to prevent XSS -->
  <dt>ID</dt>
  <dd><?= htmlspecialchars($user['id']) ?></dd>
  <dt> Email address </dt>
  <dd><?= htmlspecialchars($user['email']) ?></dd>
  <dt> Name </dt>
  <dd><?= htmlspecialchars($user['name']) ?></dd>
</dl>
