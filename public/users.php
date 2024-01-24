<?php
session_start();

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// Get member data
$sql = 'SELECT * FROM users';
$where_sql_array = [];
$prepare_params = [];

if (!empty($_GET['name'])) {
  $where_sql_array[] = ' name LIKE :name';
  $prepare_params[':name'] = '%' . $_GET['name'] . '%';
}

if (!empty($_GET['year_from'])) {
  $where_sql_array[] = 'birthday >= :year_from';
  $prepare_params [ ':year_from' ] = $_GET [ 'year_from' ] . '-01-01' ; // January 1 of the input year
}
if (!empty($_GET['year_until'])) {
  $where_sql_array[] = ' birthday <= :year_until';
  $prepare_params [ ':year_until' ] = $_GET [ 'year_until' ] . '-12-31' ; // December 31 of the input year
}

if (!empty($where_sql_array)) {
  $sql .= ' WHERE ' . implode(' AND', $where_sql_array);
}

$sql .= ' ORDER BY id DESC';

$select_sth = $dbh->prepare($sql);
$select_sth->execute($prepare_params);

// If you are logged in, get the list of following member IDs
$followee_user_ids = [];
if (!empty($_SESSION['login_user_id'])) {
  $followee_users_select_sth = $dbh->prepare(
    'SELECT * FROM user_relationships WHERE follower_user_id = :follower_user_id'
  );
  $followee_users_select_sth->execute([
    ':follower_user_id' => $_SESSION['login_user_id'],
  ]);
  $followee_user_ids = array_map(
      function ($relationship) {
          return $relationship['followee_user_id'];
      },
      $followee_users_select_sth->fetchAll()
  ); // Extract only the followee_user_id column using array_map
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






<body>
  <h1> Member list </h1>
  <div style="margin-bottom: 1em;">
    <a href ="/setting/index.php"> Settings screen </a>
    /
    <a href ="/timeline.php"> Timeline </a>
  </div>
  <div style="margin-bottom: 1em;">
    Narrow down <br>
    <form method="GET">
      名前: <input type="text" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"><br>
      Year of birth:
      <input type="number" name="year_from" value="<?= htmlspecialchars($_GET['year_from'] ?? '') ?>">年
      ~
      <input type="number" name="year_until" value="<?= htmlspecialchars($_GET['year_until'] ?? '') ?>">年
      <br>
      <button type="submit">決定</button>
    </form>
  </div>


  <?php foreach($select_sth as $user): ?>
    <div style="display: flex; justify-content: start; align-items: center; padding: 1em 2em;">
      <?php if(empty($user['icon_filename'])): ?>
        <!-- If there is no icon, display a blank space of the same size to align it -->
        <div style="height: 2em; width: 2em;"></div>
      <?php else: ?>
        <img src="/image/<?= $user['icon_filename'] ?>"
          style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <?php endif; ?>
      <a href="/profile.php?user_id=<?= $user['id'] ?>" style="margin-left: 1em;">
        <?= htmlspecialchars($user['name']) ?>
      </a>

      <div style="margin-left: 2em;">
        <?php if($user['id'] === $_SESSION['login_user_id']): ?>
          This is you!
        <?php elseif(in_array($user['id'], $followee_user_ids)): ?>
          Followed
        <?php else: ?>
          <a href ="./follow.php?followee_user_id= <?= $user [ 'id' ] ?> "> Follow </a>
        <?php endif; ?>
      </div>
    </div>
    <hr style="border: none; border-bottom: 1px solid gray;">
  <?php endforeach; ?>
</body>
