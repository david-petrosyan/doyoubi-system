<?php
$user = null;
if (!empty($_GET['user_id'])) {
  $user_id = $_GET['user_id'];
  // DBに接続
  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
  // 対象の会員情報を引く
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([
      ':id' => $user_id,
  ]);
  $user = $select_sth->fetch();
}
if (empty($user)) {
  header("HTTP/1.1 404 Not Found");
  print("そのようなユーザーIDの会員情報は存在しません");
  return;
}
// 投稿データを取得。紐づく会員情報も結合し同時に取得する。
$select_sth = $dbh->prepare(
  'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' WHERE user_id = :user_id'
  . ' ORDER BY bbs_entries.created_at DESC'
);
$select_sth->execute([
  ':user_id' => $user_id,
]);
// フォロー状態を取得
$relationship = null;
session_start();
if (!empty($_SESSION['login_user_id'])) { // ログインしている場合
  // フォロー状態をDBから取得
  $select_sth = $dbh->prepare(
    "SELECT * FROM user_relationships"
    . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
  );
  $select_sth->execute([
      ':followee_user_id' => $user['id'], // フォローされる側は閲覧しようとしているプロフィールの会員
      ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
  ]);
  $relationship = $select_sth->fetch();
}
// フォローされている状態を取得
$follower_relationship = null;
if (!empty($_SESSION['login_user_id'])) { // ログインしている場合
  // フォローされている状態をDBから取得
  $select_sth = $dbh->prepare(
    "SELECT * FROM user_relationships"
    . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
  );
  $select_sth->execute([
      ':follower_user_id' => $user['id'], // フォローしている側は閲覧しようとしているプロフィールの会員
      ':followee_user_id' => $_SESSION['login_user_id'], // フォローされる側はログインしている会員
  ]);
  $follower_relationship = $select_sth->fetch();
}

?>
<a href ="/timeline.php"> Return to timeline</a>

<div style="
    width: 100%; height: 15em;
    <?php if(!empty($user['cover_filename'])): ?>
    background: url('/image/<?= $user['cover_filename'] ?>') center;
    background-size: cover;
    <?php endif; ?>
  ">
</div>

<h1><?= htmlspecialchars($user ['name']) ?> 's profile </h1>

<div>
  <?php if(empty($user['icon_filename'])): ?>
  Not currently set
  <?php else : ?>
  <img src="/image/<?= $user['icon_filename'] ?>"
    style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>

<?php if ( $user [ 'id' ] === $_SESSION [ 'login_user_id' ]): // For yourself ?>
<div style="margin: 1em 0;">
  This is you! <br> _ _
  <a href ="/setting/index.php"> Click here for the settings screen </a>
</div>
<?php else : // For someone else ?>
<div style="margin: 1em 0;">
  <?php if (empty($relationship)): // If you are not following ?>
  <div>
    <a href ="./follow.php?followee_user_id= <?=  $user [ 'id' ] ?>"> Follow </a>
  </div>
  <?php else : // If you are following ?>
  <div>
     You followed <?= $relationship [ 'created_at' ] ?> .
  </div>
  <?php endif; ?>
  <?php if (!empty( $follower_relationship )): // If followed ?>
  <div>
    You are being followed.
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if(!empty($user['birthday'])): ?>
<?php
  $birthday = DateTime::createFromFormat('Y-m-d', $user['birthday']);
  $today = new DateTime('now');
?>
  <?= $today ->diff($birthday)->y ?> years old
<?php endif; ?>


<div>
  <?= nl2br(htmlspecialchars($user['introduction'] ?? '')) ?>
</div>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt> Date and time </dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt> Content </dt>
    <dd>
      <?= htmlspecialchars($entry['body']) ?>
      <?php if(!empty($entry['image_filename'])): ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>
