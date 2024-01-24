<?php
// connect to DB
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if(!empty($_POST['email']) && !empty($_POST['password'])) {
  // Process login only if email and password are sent via POST

  // Get member information from email
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
  $select_sth->execute([
    ':email' => $_POST['email'],
  ]);
  $user = $select_sth->fetch();

  if(empty($user)) {
    // If no member matching the entered email address is found, the process is interrupted and redirected to the login screen URL with error query parameters
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }

  // Seperate hash part and salt
  
  $correct_password = password_verify($_POST['password'], $user['password']);

  if(!$correct_password) {
    // If the password is incorrect, interrupt the process and redirect to the login screen URL with error query parameters
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }


  # Original implementation of the session from here (For details, refer to the first class in the second semester) ##############
  // Get session ID (if not, create and set a new one)
  session_start();


  $_SESSION["login_user_id"] = $user['id'];
  # Session so far ############################################ #####


  // If login is successful, redirect to login completion screen
  header("HTTP/1.1 302 Found");
  header("Location: ./login_finish.php");
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
 margin: auto;
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
 width: 40%;
 margin: auto;
 color: ;
 font-size: 2em;
 

}

a {
 text-decoration: none;
}

</style>
</html>




<h1> Login </h1>
<p>if you are a first time user, please <a href="/signup.php"> register as a member </a>.</p>
<hr>

<!-- Login form -->
<form method="POST">
  <!-- The type attribute of the input element all works with text, but setting it to an appropriate value makes it easier for users to use -->
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

<?php  if(!empty($_GET ['error'])): // Display error message if there is a query parameter for error ?>
<div style="color: red;">
  Your email address or password is incorrect.
</div>
<?php endif; ?>
