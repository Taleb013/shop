<?php
$m=new mysqli('127.0.0.1','root','','shop_db');
$r=$m->query('SHOW INDEX FROM product');
while($row=$r->fetch_assoc()){ echo $row['Key_name'].' '.$row['Column_name'].' '.$row['Non_unique'].'\n'; }
$m->close();
?>