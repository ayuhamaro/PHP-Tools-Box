<?php
$lock_path = "/var/www/html/lock/";
$lock_files = glob("$lock_path*.lock");
$exp_time = ((int)date('U')) - 3600;

if(count($lock_files) >= 10000){
    foreach($lock_files as $file_path){
        if(file_exists($file_path)){
            if(filemtime($file_path) < $exp_time){
                @unlink($file_path);
            }
        }
    }
}
?>
