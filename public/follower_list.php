<?php
session_start();

// Skip to login screen if not logged in
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// connect to DB
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// Get the list of people being followed from the DB.
// Use table join to get the member information of the following target as well.
$select_sth = $dbh->prepare(
  'SELECT user_relationships.*, users.name AS follower_user_name, users.icon_filename AS follower_user_icon_filename'
  . ' FROM user_relationships INNER JOIN users ON user_relationships.follower_user_id = users.id'
  . ' WHERE user_relationships.followee_user_id = :followee_user_id'
  . ' ORDER BY user_relationships.id DESC'
);
$select_sth->execute([
    ':followee_user_id' => $_SESSION['login_user_id'],
]);
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




<h1> Followed list </h1>

<ul>
  <?php foreach($select_sth as $relationship): ?>
  <li> _ _
    <a href="/profile.php?user_id=<?= $relationship['follower_user_id'] ?>">
      <?php if(!empty($relationship[ 'follower_user_icon_filename' ])): // Display icon image if available ?>
      <img src="/image/<?= $relationship['follower_user_icon_filename'] ?>"
        style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <?php endif; ?>

      <?= htmlspecialchars($relationship['follower_user_name']) ?>
      (ID: <?= htmlspecialchars($relationship['follower_user_id']) ?>)
    </a>
    (Followed by <?= $relationship [ 'created_at' ] ?>)
  </li> _ _
  <?php endforeach; ?>
</ul>
