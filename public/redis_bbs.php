<?php

$redis = new Redis();
$redis->connect('redis', 6379);

$key = 'bbs_kakikomi';

$kakikomi = $redis->exists($key) ? $redis->get($key) : '';

if (!empty($_POST['kakikomi'])) {
	$kakikomi = $_POST['kakikomi'];
	$redis->set($key, strval($kakikomi));
		return header('Location: ./redis_bbs.php');
}
?>

<form method="POST".
	<textarea name="kakikomi"></textarea><br>
	<button type="submit">submit</button>
</form>
<br>
<hr>
content<br>
<br>
<div><?=n12br(htmlspecialchars($kakikomi))?></div>
