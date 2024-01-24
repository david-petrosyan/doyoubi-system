<?php
  $exif = exif_read_data( './images/car.jpg' );
?>

< img  src = ./images/car.jpg " style = width: 200px " > < br >
The exif information for this image is below. < br >
<?= nl2br(print_r( $ exif , true )); ?>
