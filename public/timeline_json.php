<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

session_start();
if (empty($_SESSION [ 'login_user_id' ])) { // Not available if not logged in Return empty with 401
  header("HTTP/1.1 401 Unauthorized");
  header("Content-Type: application/json");
  print(json_encode(['entries' => []]));
  return;
}

// Get current login information
$user_select_sth = $dbh->prepare("SELECT * from users WHERE id = :id");
$user_select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $user_select_sth->fetch();

// Get post data. In order to use placeholders in the IN clause, as many ``?'' characters as there are elements in $target_user_ids are added.
$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries'
  . ' INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' WHERE'
  . '   bbs_entries.user_id IN'
  . '     (SELECT followee_user_id FROM user_relationships WHERE follower_user_id = :login_user_id)'
  . '   OR bbs_entries.user_id = :login_user_id'
  . ' ORDER BY bbs_entries.created_at DESC';
$select_sth = $dbh->prepare($sql);
$select_sth->execute([
  ':login_user_id' => $_SESSION['login_user_id'],
]);

// Prepare a function to output the body HTML
function bodyFilter (string $body): string
{
  $body = htmlspecialchars($body); // Escape processing
  $body = nl2br($body); // Convert newline character to <br> element

  // Make a string such as >>1 an in-page link to the post with the corresponding number (response anchor function)
  // Be careful as ">" (half-width greater-than symbol) is escaped with htmlspecialchars()
  $body = preg_replace('/>>(\d+)/', '<a href="#entry$1">>>$1</a>', $body);

  return $body;
}

// Entries for outputting to JSON
$result_entries = [];
foreach ($select_sth as $entry) {
  $result_entry = [
    'id' => $entry['id'],
    'user_name' => $entry['user_name'],
    'user_icon_file_url' => empty($entry['user_icon_filename']) ? '' : ('/image/' . $entry['user_icon_filename']),
    'user_profile_url' => '/profile.php?user_id=' . $entry['user_id'],
    'body' => bodyFilter($entry['body']),
    'image_file_url' => empty($entry['image_filename']) ? '' : ('/image/' . $entry['image_filename']),
    'created_at' => $entry['created_at'],
  ];
  $result_entries[] = $result_entry;
}

header("HTTP/1.1 200 OK");
header("Content-Type: application/json");
print(json_encode(['entries' => $result_entries]));
