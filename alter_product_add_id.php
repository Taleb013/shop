<?php
$m=new mysqli('127.0.0.1','root','','shop_db');
if($m->connect_errno){echo 'connect error';exit;}
$sql="ALTER TABLE product ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
if($m->query($sql)){
    echo 'ALTER OK\n';
} else {
    echo 'ALTER FAILED: '. $m->error ."\n";
}
$m->close();
?>