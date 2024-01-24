<?php
// connect to DB
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if(!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
	// If name, email, and password are sent via POST, register them in the DB

	// insert//
  $insert_sth = $dbh->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
  $insert_sth->execute([
    ':name' => $_POST['name'],
    ':email' => $_POST['email'],
    ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
  ]);

  // When the process is finished, redirect to the completion screen
  header("HTTP/1.1 302 Found");
  header("Location: /signup_finish.php");
  return;
}
?>


<html>
<style>

body{
 background: linear-gradient(#e66465, #9198e5);
}

h1 {
 text-align: center;
 margin: auto;
 width: 400px;
 height: 40px;
 background: ;
 color: #bbb;
 font-size: 1.8em;
 border-radius: ;
}

form{
 width: 40%;
 margin:auto;
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
 width: 40%;
 margin: auto;
}


a {
 text-decoration: none;
}

</style>
</html>


<h1> Member registration </h1>

<p>If you are already a registered member, please <a href="/login.php"> Log in </a>.</p>
<hr>

<!-- Registration form -->
<form method="POST">
  <!-- The type attribute of the input element all works with text, but setting it to an appropriate value makes it easier for users to use -->
  <label>
    name:
    <input type="text" name="name">
  </label>
  <br>
  <label>
    email address:
    <input type="email" name="email">
  </label>
  <br>
  <label>
    password:
    <input type="password" name="password" min="6" autocomplete="new-password">
  </label>
  <br>
  <button type="submit">決定</button>
</form>

<?php if(!empty($_GET['duplicate_email'])): ?>
<div style="color: red;">
  入力されたメールアドレスは既に使われています。
</div>
<?php endif; ?>
