<?php
$dbh = new  PDO ('mysql:host=mysql;dbname=techc' , 'root' , '');

$insert_sth = $dbh -> prepare (" INSERT INTO hogehoge (text) VALUES (:text) ");
$insert_sth -> execute ([
    ':text' => 'hello world!!!!!!!!!'
]);
print( 'insert was successful' );;
