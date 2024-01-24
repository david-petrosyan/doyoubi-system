<?php
$dbh = new  PDO ( 'mysql:host=mysql;dbname=techc' , 'root' , '' );

if (isset( $_POST [ 'body' ])) {
  // If there is a form parameter body sent by POST

  // INSERT into the hogehoge table
  $insert_sth = $dbh ->prepare (" INSERT INTO hogehoge (text) VALUES (:body) ");
  $insert_sth ->execute ([
      ':body' => $_POST [ 'body' ],
  ]);

  // redirect when done
  // If you don't redirect, you'll post again with the same content when reloading
  header(" HTTP/1.1 302 Found ");
  header(" Location: ./enshu2.php ");
  return ;
}

// Get page count from URL query parameter. If there is none, it is regarded as the first page
$page = isset( $_GET [ 'page' ]) ? intval( $_GET [ 'page' ]) : 1 ;

// determine the number of lines per page
$count_per_page = 10 ;

// calculate the number of lines to skip depending on the number of pages
$skip_count = $count_per_page * ( $page - 1 );

// Get the number of rows in the hogehoge table with SELECT COUNT
$count_sth = $dbh ->prepare ( 'SELECT COUNT(*) FROM hogehoge;' );
$count_sth ->execute ();
$count_all = $count_sth ->fetchColumn ();
if ($skip_count >= $count_all ) {
    // If the number of skipped lines is more than the total number of lines, it is strange, so display an error message and exit
    print( 'This page does not exist!' );
    return ;
}

// get data from hogehoge table
$select_sth = $dbh ->prepare ( 'SELECT * FROM hogehoge ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_count' );
// Use bindParam() to bind a numeric value instead of a string to the placeholder, and pass a constant to the third argument to indicate that it is an INT
$select_sth ->bindParam ( ':count_per_page' , $count_per_page , PDO :: PARAM_INT );
$select_sth ->bindParam ( ':skip_count' ,$skip_count , PDO :: PARAM_INT );
$select_sth ->execute ();
?>

<!-- Post the form to this file itself -->
< form  method =" POST " action =" ./formtest2.php" >
  < textarea  name = " body " > </textarea> _
  < button  type =" submit " > submit </ button >
</form>

< hr  style =" margin: 3em 0; " > </ hr >

< div  style =" width: 100%; text-align: center; padding-bottom: 1em; border-bottom: 1px solid #ccc; margin-bottom: 0.5em " >
  <?=  $page  ?> page
  (all <?= floor($count_all / $count_per_page ) + 1  ?> out of pages)

  < div  style =" display: flex; justify-content: space-between; margin-bottom: 2em; " >
    <div> _ _
      <?php  if ( $page > 1 ): // Show previous page if there is one ?>
        < a  href =" ?page= <?=  $page - 1  ?> " > previous page </ a >
      <?php  endif ; ?>
    </div> _ _
    <div> _ _
      <?php  if ( $count_all > $page * $count_per_page ): // Show next page if there is one ?>
        < a  href ="?page= <?=  $page + 1  ?> " > next page </ a >
      <?php  endif ; ?>
    </div>
  </div>
</div>

<?php  foreach ( $select_sth  as  $row ): ?>
  < dl  style =" margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc; " >
    < dt > Sent date and time </ dt >
    < dd > <?=  $row [ 'created_at' ] ?> </ dd >
    < dt > Content of transmission </ dt >
    < dd > <?= nl2br(htmlspecialchars($row [ 'text' ])) ?> </ dd >
  </dl>
<?php  endforeach  ?>
