<?php
function browser_info($agent=null) {
  // Declare known browsers to look for
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
    'konqueror', 'gecko');

  // Clean up agent and build regex that matches phrases for known browsers
  // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
  // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  foreach($known as $b)
	if(strpos($agent,$b)!==false)
		return $b;
}

include_once("conf.inc.php");
include_once("login.php");
global $database,$dochome;

$login = new NethServiceAuth();

$login->Authenticate($_SESSION['name'],$_SESSION['password']);

$auth = $login->isLoggedIn();


if($auth=='1') {
	$fileid=$_GET["id"];
       	$type = mysql_escape_string('view');
       	$details = mysql_escape_string($fileid);
       	$query = "insert into $GLOBALS[tablePrefix]log set user=\"$_SESSION[user]\",ip=\"$_SERVER[REMOTE_ADDR]\",type=\"$type\",details=\"$details\"";
       	$result = mysql_query($query,$database);
       	$query1 = "update $GLOBALS[tablePrefix]fax set downloads=downloads+1 where id='$fileid';";
       	$result1 = mysql_query($query1,$database);
       	$query2 = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where id='".$fileid."';";
       	$result2 = mysql_query($query2,$database);
       	$file = mysql_fetch_assoc($result2);

       	$name = $dochome.$file[rpath]."/$file[filename]";

	$ua = browser_info();
	if($ua != 'msie' && (substr($file['filename'],-3) == "tif" || substr($file['filename'],-4) == "tiff") ) //use alterantiff if not IE
	{
	     $fp = fopen($name, 'rb');	 
 	 
             header("Content-Type: image/tiff");	 
             header("Content-Length: " . filesize($name));	 
             header("Content-Disposition: inline; filename=\"".basename($name)."\"");	 
 	 
             fpassthru($fp);	 
             fclose($fp);	 
             exit;
	}
	else
	{
      	   header("Pragma: public");
      	   header("Expires: 0");
      	   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
     	   header("Cache-Control: private",false);
     	   header("Content-Type: application/octet-stream");
      	   header("Content-Disposition: attachment; filename=\"{$file['filename']}\"");
      	   header("Content-Length: ".filesize($name));
	   readfile($name);
	}
}
