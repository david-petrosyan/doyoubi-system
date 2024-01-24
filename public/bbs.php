<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
session_start();
// Get post data. Linked member information is also combined and acquired at the same time.
$select_sth = $dbh->prepare(
  ' SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' ORDER BY bbs_entries.created_at DESC'
);
$select_sth->execute();
// Prepare a function to output the body HTML
function bodyFilter (string $body): string
{
    $body = htmlspecialchars( $body ); // Escape processing
    $body = nl2br( $body ); // Convert newline character to <br> element
    // Make a string such as >>1 an in-page link to the post with the corresponding number (response anchor function)
    // Be careful as ">" (half-width greater-than symbol) is escaped with htmlspecialchars()
    $body = preg_replace('/>>(\d+)/', '<a href="#entry$1">>>$1</a>', $body);
    return $body;
}
?>
<?php if(empty($_SESSION['login_user_id'])): ?>
  <a href ="/login.php"> Login</a>to view your timeline!
<?php else: ?>
  <a href ="/timeline.php"> Click here for timeline</a>
<?php endif; ?>
<hr>
<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt id="entry<?= htmlspecialchars($entry['id']) ?>">
      number
    </dt>
    <dd>
      <?= htmlspecialchars($entry['id']) ?>
    </dd>
    <dt>
      Contributor
    </dt>
    <dd>
      <a href="/profile.php?user_id=<?= $entry['user_id'] ?>">
        <?php if (!empty( $entry [ 'user_icon_filename' ])): // Show icon image if there is one ?>
        <img src="/image/<?= $entry['user_icon_filename'] ?>"
          style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
        <?php endif; ?>
        <?= htmlspecialchars($entry['user_name']) ?>
        (ID: <?= htmlspecialchars($entry['user_id']) ?>)
      </a>
    </dd>
    <dt> Date and time </dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt> Content </dt>
    <dd>
      <?= bodyFilter($entry['body']) ?>
      <?php if(!empty($entry['image_filename'])): ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>

