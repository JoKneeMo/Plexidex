<?php 

/** 
 ***Original***
 * @author Edgar Treml 
 * @copyright 2015
 ***Modifications*** 
 * @author Johnny Moore 
 * @copyright 2016 
 * This script get's the thumbnail and displays it. 
 * This script is neccesarry beacause we don't want anybody to know your Plex Token 
 */ 
header('Content-type: text/html; charset=utf-8');
include 'settings.inc'; 

if (isset($_GET['url']) && ($_GET['url']!='')) { 
     
    $filename='./temp/'.str_replace('/','_',$_GET['url']).'.jpg'; 
    /** if we have a saved thumb, let's take the saved version **/ 
    if (is_file($filename)) { 
        $img=imagecreatefromjpeg($filename); 
        header('Content-Type: image/jpeg'); 
        ImageJpeg($img,NULL,75); 
        exit; 
    } 
     
    $img=imagecreatefromjpeg('http://' . $PlexServerIP . ':' . $PlexServerPort . 
        $_GET['url'].'?X-Plex-Token=' . $PlexServerToken); 
         
    /** resize the thumb and save it **/ 
    $SmallImg=imagecreatetruecolor(560,850); 
    ImageCopyResampled($SmallImg,$img,0,0,0,0,560,840,Imagesx($img),Imagesy($img)); 
    @ImageJpeg($SmallImg,$filename,75); 
} 
else { 
    $img=imagecreatetruecolor(560,840); 
    $background_color = ImageColorAllocate ($img, 255, 255, 255); 
} 
header('Content-Type: image/jpeg'); 
ImageJpeg($img,NULL,75); 

?>
