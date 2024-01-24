<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./login.php");
  return;
}
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
// フォロー対象(フォローされる側)のデータを引く
$followee_user = null;
if (!empty($_GET['followee_user_id'])) {
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([
      ':id' => $_GET['followee_user_id'],
  ]);
  $followee_user = $select_sth->fetch();
}
if (empty($followee_user)) {
  header("HTTP/1.1 404 Not Found");
  print("そのようなユーザーIDの会員情報は存在しません");
  return;
}
// 現在のフォロー状態をDBから取得
$select_sth = $dbh->prepare(
  "SELECT * FROM user_relationships"
  . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
);
$select_sth->execute([
    ':followee_user_id' => $followee_user['id'], // フォローされる側(フォロー対象)
    ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
]);
$relationship = $select_sth->fetch();
if (!empty($relationship )) { // If there is already a following relationship, display an appropriate error and exit
  print("You are already following.");
  return;
}

$insert_result = false;
if ( $_SERVER [ 'REQUEST_METHOD' ] == 'POST' ) { // If POST is done using the form, perform the actual follow registration process
  $insert_sth = $dbh->prepare(
    "INSERT INTO user_relationships (follower_user_id, followee_user_id) VALUES (:follower_user_id, :followee_user_id)"
  );
  $insert_result = $insert_sth->execute([
      ':followee_user_id' => $followee_user [ 'id' ], // Followed party (followee target)
      ':follower_user_id' => $_SESSION [ 'login_user_id' ], // Follower is logged in member
  ]);
}
?>

<?php if($insert_result): ?>
<div>
  I followed <?= htmlspecialchars( $followee_user [ 'name' ]) ?> . <br> _ _
  <a href="/profile.php?user_id=<?= $followee_user['id'] ?>">
       <?= htmlspecialchars( $followee_user [ 'name' ]) ?> 's profile
  </a>
  /
  <a href="/users.php">
    Go to member list
  </a>
</div>
<?php else : ?>
<div>
  Would you like to follow <?= htmlspecialchars( $followee_user [ 'name' ]) ?> ?
  <form method="POST">
    <button type="submit">
      to follow
    </button>
  </form>
</div>
<?php endif; ?>
