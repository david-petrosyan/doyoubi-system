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
 color: white;
 font-size: 2em;
 margin-left: 15px;

}
dl {
 padding: 20px;
 width: 20%;
 border-top: #bbb 4px solid;
 border-bottom: #bbb 4px solid;
 font: bold;
 color: purple;
}
ul {
 font: bold;
 font-size: 1.4em;
 color: white;
}
a {
 text-decoration: none;
}

</style>
</html>





<a href ="/timeline.php">Return to timeline</a>

<h1> Settings screen </h1>

<p>
  Current info
</p>
<dl>  <!-- When outputting registration information, be sure to use htmlspecialchars() to prevent XSS -->
  <dt>ID</dt>
  <dd><?= htmlspecialchars($user['id']) ?></dd>
  <dt> Email address </dt>
  <dd><?= htmlspecialchars($user['email']) ?></dd>
  <dt> Name </dt>
  <dd><?= htmlspecialchars($user['name']) ?></dd>
</dl>

<ul>
  <li><a href ="./name.php "> Name settings </a></li>
  <li><a href ="./icon.php "> Icon settings </a></li>
  <li><a href ="./cover.php "> Cover image settings </a></li>
  <li><a href ="./birthday.php "> Birthdate settings </a></li>
  <li><a href ="./introduction.php "> Self-introduction text settings </a></li>
  <li><a href="../follower_list.php"> Followers </a></li>
</ul>
