<?php

include_once("conf.inc.php");
include_once("login.php");

$login = new NethServiceAuth();

$login->Authenticate($_SESSION['name'],$_SESSION['password']);

$auth = $login->isLoggedIn();

global $database,$dochome;

if($auth=='1') {

       $fileid=basename($_GET["id"]);
       $dir=dirname($_GET["id"]);
       $name = $dochome."/thumb/$fileid";
       /*if(strpos($_GET["id"],'sentm')!==false)
       {
         $name=$dochome."/thumb/$fileid";
	 $dir="/sent";
       }*/
       if(!file_exists($name) || filesize($name)==0) 
       {
	 $tmp=explode(".",$fileid);
	 $base=$tmp[0];
	 $base=substr($base,0,-3); #rimuove -15 finale
	 $file = $base.".png";
     exec("/usr/bin/convert  -depth 16 -alpha off -type Grayscale -adaptive-resize 500 $dochome/$dir/$base.*[0] $name");
	 #exec("/bin/chmod 644 $dochome/thumb/$fileid");
	 #cancello i file intermedi
       }
       // apre il file in modalita' binaria
       $fp = fopen($name, 'rb');
 
       // invia i giusti header
       header("Content-Type: image/png");
 
       // invia l'immagine ed esce dallo script
       fpassthru($fp); 
       fclose($fp); 
       exit;
} else {

      header("Location: index.php");
}

?>
