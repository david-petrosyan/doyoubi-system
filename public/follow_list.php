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

// Pull the list of people you are following from the DB.
// Use table join to get the member information of the following target as well.
$select_sth = $dbh->prepare(
  'SELECT user_relationships.*, users.name AS followee_user_name, users.icon_filename AS followee_user_icon_filename'
  . ' FROM user_relationships INNER JOIN users ON user_relationships.followee_user_id = users.id'
  . ' WHERE user_relationships.follower_user_id = :follower_user_id'
  . ' ORDER BY user_relationships.id DESC'
);
$select_sth->execute([
    ':follower_user_id' => $_SESSION['login_user_id'],
]);
?>

<h1> Following list </h1>

<ul>
  <?php foreach($select_sth as $relationship): ?>
  <a href="/profile.php?user_id=<?= $relationship['followee_user_id'] ?>">
    <?php  if (!empty($relationship [ 'followee_user_icon_filename' ])): // Display icon image if available ?>
    <img src="/image/<?= $relationship['followee_user_icon_filename'] ?>"
      style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
    <?php endif; ?>

    <?= htmlspecialchars($relationship['followee_user_name']) ?>
    (ID: <?= htmlspecialchars($relationship['followee_user_id']) ?>)
  </a>
  (follow <?= $relationship ['created_at'] ?>)
  <?php endforeach; ?>
</ul>
