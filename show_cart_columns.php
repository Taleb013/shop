<?php
$m=new mysqli('127.0.0.1','root','','shop_db');
if($m->connect_errno){echo 'connect error';exit;}
$r=$m->query('SHOW COLUMNS FROM cart');
if(!$r){echo 'query error: '.$m->error; exit;}
while($row=$r->fetch_assoc()){echo $row['Field'].' '.$row['Type'].'\n';}
$m->close();
?>