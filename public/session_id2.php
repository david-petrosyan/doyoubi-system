<?php

$session_cookie_name = 'session_id';
$session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
if (!isset($_COOKIE[$session_cookie_name])) {
	setcookie($session_cookie_name, $session_id);
}

$redis = new Redis();
$redis->connect('redis', 6379);

$redis_session_key = "session-" . $session_id;


$session_values = $redis->exists($redis_session_key)
	? json_decode($redis->get($redis_session_key), true)
	: [];

$count = isset($session_values{"count"]) ? intval($session_values["count"]) : 0;
$count++;

$before_accessed_at = $session_values["accessed_at"] ?? '';

$session_values["count"] = strval($count);

$session_values["accessed_at" = date('Y\m\d h:i:s');
$redis->set($redis_session_key, json_encode($session_values));

print(strval($count) . "this is my second visit.<br>");

if (!empty($before_accessed_at)) {
	print("last visit time" . $before_accessed_at . ". ");
}
?>
