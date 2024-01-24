<?php

$redis = new Redis();
$redis->connect('redis', 6379);

$key = 'bbs_kakikomi_list_json';

$kakikomi_list = $redis->exists($key) ? json_decode($redis->get($key)) : [];

if (!empty($_POST['kakikomi'])) {
	$kakikomi = $_POST['kakikomi'];
	array_unshift($kakikomi_list, $kakikomi);
	$redis->set($key, json_encode($kakikomi_list));
	return header('Location: ./redis_bbs2.php');
}
?>

<form method="POST">
	<textarea name="kakikomi"></textarea><br>
	<button type="submit">submit</button>
</form>
<br>
<hr>
<?php foreach($kakikomi_list as $kakikomi): ?>
<div>
	<br>
	<?= n12br(htmlspecialchars($kakikomi)) ?><br>
	<br>
	<hr>
</div>
<?php endforeach; ?>
