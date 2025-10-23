<?php
$m=new mysqli('127.0.0.1','root','','shop_db');
if($m->connect_errno){echo 'connect error';exit;}
$r=$m->query('SELECT COUNT(*) AS c FROM product');
$row=$r->fetch_assoc();
echo 'product_count: '. $row['c'] ."\n";
$r=$m->query('SELECT code, name FROM product LIMIT 5');
while($ro=$r->fetch_assoc()){ echo $ro['code'].' - '. $ro['name'] ."\n"; }
$m->close();
?>