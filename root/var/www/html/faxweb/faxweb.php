<?php

if(!isset($resource)) $resource = "0";
// session initilization

include_once("conf.inc.php");
include_once("login.php");

if($resource != true){
	if(isset($_GET['faxweb'])){$_POST['faxweb']=$_GET['faxweb'];}
	if(isset($_POST['faxweb'])){
		switch($_POST['faxweb']){
			case "userLogin":
				if(isset($_POST['username'],$_POST['password'])){
					userLogin($_POST['username'],$_POST['password']);
				}else{
					error("username and password are both required");
				}
				break;
			case "userLogoff":
				userLogoff();
				break;
			case "checkLogin":
                                $how='0';
				checkLogin($how);
				break;
			case "search":
				if(isset($_POST['terms'],$_POST['path'])){
					search($_POST['terms'],$_POST['path']);
				}
				break;
			case "advSearch":
				if(isset($_REQUEST['limits'],$_REQUEST['path'],$_REQUEST['check'],$_REQUEST['from'],$_REQUEST['to'],$_REQUEST['name'],$_REQUEST['number'],$_REQUEST['tag'],$_REQUEST['esito'],$_REQUEST['letto'],$_REQUEST['send'])){
					advSearch($_REQUEST['limits'],$_REQUEST['path'],$_REQUEST['check'],$_REQUEST['from'],$_REQUEST['to'],$_REQUEST['name'],$_REQUEST['number'],$_REQUEST['tag'],$_REQUEST['esito'],$_REQUEST['letto'],$_REQUEST['send']);
				}
				break;
			case "getFolder":
				if(isset($_REQUEST['path'],$_REQUEST['page'])){
					getFolder($_REQUEST['path'],$_REQUEST['page']);
				}
				break;
			case "getForward":
				if(isset($_REQUEST['type'])){
					getForward($_REQUEST['type']);
				}
				break;
			case "faxStat":
				faxStat();
				break;
			case "sendMail":
				if(isset($_REQUEST['mail'],$_REQUEST['id'],$_REQUEST['note'])){
					sendMail($_REQUEST['id'],$_REQUEST['mail'],$_REQUEST['note']);
				}
				break;
			case "addAddress":
				if(isset($_REQUEST['fax'],$_REQUEST['address'])){
					addAddress($_REQUEST['fax'],$_REQUEST['address']);
				}
				break;
			case "ResendFax":
				if(isset($_REQUEST['id'],$_REQUEST['type'])){
					ResendFax($_REQUEST['id'],$_REQUEST['type']);
				}
				break;
			case "ResendError":
				if(isset($_REQUEST['path'])){
					ResendError($_REQUEST['path']);
				}
				break;
                        case "StopAll":
                                if(isset($_REQUEST['path'])){
                                        StopAll($_REQUEST['path']);
                                }
				break;
			case "FaxLetto":
				if(isset($_REQUEST['id'])){
					FaxLetto($_REQUEST['id']);
				}
				break;
			case "addRead":
				if(isset($_REQUEST['id'],$_REQUEST['letto'])){
					addRead($_REQUEST['id'],$_REQUEST['letto']);
				}
				break;
			case "stopSend":
				if(isset($_REQUEST['job_id'])){
					stopSend($_REQUEST['job_id']);
				}
				break;
			case "getFile":
				if(isset($_GET['fileid'])){
					getFile($_GET['fileid']);
				}
				break;
			case "getFilePackage":
				if(isset($_GET['fileid'])){
					getFilePackage($_GET['fileid']);
				}
				break;
			case "emailFilePackage":
				if(isset($_GET['fileid'],$_GET['to'],$_GET['from'],$_GET['message'])){
					emailFilePackage($_GET['fileid'],$_GET['to'],$_GET['from'],$_GET['message']);
				}
				break;	
			case "getMeta":
				if(isset($_POST['fileid'])){
					getMeta($_POST['fileid']);
				}
				break;
			case "getFolderMeta":
				if(isset($_POST['path'])){
					getFolderMeta($_POST['path']);
				}
				break;
			case "setMeta":
				if(isset($_POST['fileid'],$_POST['description'],$_POST['flags'])){
					setMeta($_POST['fileid'],$_POST['description'],$_POST['flags']);
				}
				break;
			case "fileRename":
				if(isset($_POST['fileid'],$_POST['filename'])){
					fileRename($_POST['fileid'],$_POST['filename']);
				}
				break;
			case "fileMove":
				if(isset($_POST['fileid'],$_POST['path'])){
					fileMove($_POST['fileid'],$_POST['path']);
				}
				break;
			case "fileDelete":
				if(isset($_POST['fileid'])){
					fileDelete($_POST['fileid']);
				}
				break;
			case "folderRename":
				if(isset($_POST['path'],$_POST['name'],$_POST['newname'])){
					folderRename($_POST['path'],$_POST['name'],$_POST['newname']);
				}
				break;
			case "folderMove":
				if(isset($_POST['name'],$_POST['path'],$_POST['newpath'])){
					folderMove($_POST['name'],$_POST['path'],$_POST['newpath']);
				}
				break;
			case "folderDelete":
				if(isset($_POST['folder'])){
					folderDelete($_POST['folder']);
				}
				break;
			case "newFolder":
				if(isset($_POST['name'],$_POST['path'])){
					newFolder($_POST['name'],$_POST['path']);
				}
				break;
			case "fileUpload":
				if(isset($_POST['path'])){
					uploadFiles($_POST['path']);
				}
				break;
			case "upload":
				if(isset($_POST['dir'])){
					upload($_POST['dir']);
				}	
				break;
			case "uploadSmart":
 				        uploadSmart();
				break;
			case "uploadAuth":
				if(isset($_POST['path'])){
					uploadAuth($_POST['path']);
				}
				break;
			case "thumbnail":
				if(isset($_POST['fileid'])){
					thumbnail($_POST['fileid']);
				}
				break;
			case "newPassword":
				if(isset($_POST['currentPassword'],$_POST['newPassword'])){
				        newPassword($_POST['currentPassword'],$_POST['newPassword']);
				}
				break;
			case "getThumb":
				if(isset($_GET['fileid'])){
				getThumb($_GET['fileid']);
				}
				break;
                        case "countFax":
                                if(isset($_REQUEST['path'])){
                                countFax($_REQUEST['path']);
                                }
				break;
			}
		}
	;}
//$data= $rootPath;
//$app = fopen("/tmp/debug",'a');
//$data = fwrite($app,$data."\n");
//fclose($app);
function escape($string) {

$find = array('\\', '"', '/', "\b", "\f", "\n", "\r", "\t", "\u","<",">");
$repl = array('\\\\', '\"', '\/', '\b', '\f', '\n', '\r', '\t', '\u',"\<","\>");

$string = str_replace($find, $repl, $string);

return $string;

}
function countFax($path) {
	global $database,$limit;

	$output = '';
	jsonStart();
	$fullpath = getUserPath($path).$path;
        $where= getPermissions();

        $query = "select COUNT(*) as tot from $GLOBALS[tablePrefix]fax where (path='$fullpath' and status='found' and deleted!='1') $where;";
	$result = mysql_query($query,$database);
	$tot = mysql_result($result,0);

        if ($tot <= $limit) $page = 1;
        else { $page = ($tot / $limit) + 1;
               $page = intval($page); }
        $output .= jsonAdd("\"page\": \"$page\"");
        $output .= jsonReturn('countFax');

	echo $output;

}
function advSearch ($limits,$path,$check,$from,$to,$name,$number,$tag,$esito,$letto,$send) {
	global $database,$dateFormat,$fileinfo;

	jsonStart();
	$name = mysql_escape_string($name);
	$number = mysql_escape_string($number);
	$tag = mysql_escape_string($tag);
        $where= getPermissions(); 
	$where2='';
	if($from!='' && $to!='') {
					$tmp=preg_split('/ /',$from);
					$tmp2=preg_split('/-/',$tmp[0]);
					$from=$tmp2[2]."-".$tmp2[1]."-".$tmp2[0]." ".$tmp[1];

					$tmp=preg_split('/ /',$to);
					$tmp2=preg_split('/-/',$tmp[0]);
					$to=$tmp2[2]."-".$tmp2[1]."-".$tmp2[0]." ".$tmp[1];
		   		 }
	if(preg_match("/received/", $path)) { 
					   if($check=='1') $where2 = "and path like '".$path."%'";
					   else if($check=='0') $where2 = "and path ='".$path."'";
					   $from = preg_replace("/-/",":",$from);
					   $to = preg_replace("/-/",":",$to);

					   if($letto=="read") $where2 = $where2." and letto!='0'";
					   else if($letto=="unread") $where2 = $where2." and letto='0'";

					   if($send=="send") $where2 = $where2." and forward_rcp!=''";
					   else if($send=="unsend") $where2 = $where2." and forward_rcp=''";

	} else if(preg_match("/sent/", $path)) {
					   if($check=='1') $where2 = "and path like '".$path."%'";
					   else if($check=='0') $where2 = "and path ='".$path."'";

					   if($esito=="ok") $where2 = $where2." and state='7'";
					   else if($esito=="ko") $where2 = $where2." and state!='7'";
	}
	if($name!=Null) $where2 = $where2." and name like '%".$name."%'";

	if($number!=Null) $where2 = $where2." and number like '%".$number."%'";

	if($tag!=Null) $where2 = $where2." and description like '%".$tag."%'";

	if($from!=Null && $to!=Null) $where2 = $where2." and date > '".$from."' and date < '".$to."'";

	$query=" select COUNT(*) as tot from $GLOBALS[tablePrefix]fax where deleted != '1' $where $where2;";
	$result = mysql_query($query,$database);
	$tot = mysql_result($result,0);

	if($limits!='all') {
		   $query2 = "select *,date_format(`date`,\"$dateFormat\") as `dateformatted` from $GLOBALS[tablePrefix]fax where deleted != '1' $where $where2 order by date desc limit $limits;"; 
	} else if ($limits=='all'){
		   $query2 = "select *,date_format(`date`,\"$dateFormat\") as `dateformatted` from $GLOBALS[tablePrefix]fax where deleted != '1' $where $where2 order by date desc;"; 
		   $limits=$tot;
	}
	if($tot>=$limits) $view=$limits;
	else if ($tot<$limits) $view=$tot;
	$result2 = mysql_query($query2,$database);
	$result3 = array();
	while($files = mysql_fetch_assoc($result2)) array_push($result3, $files);

	$result3 = array_reverse($result3, false);
	$myrank='0';

	for ($i = 0; $i < count($result3); $i++) {
		   $files = $result3[$i];
		   getFileInfo($files['id']);

                   $tot_esc= escape($tot);
		   $view_esc= escape($view);
                   $myrank_esc= escape($myrank);
                   $fileinfo_esc= escape($fileinfo[virtualpath]);
                   $pippo = array();
                   foreach ($files as $key => $value) { $pippo[$key] = escape($value); }

		   jsonAdd("\"tot\":\"$tot_esc\",\"view\":\"$view_esc\",\"rank\":\"$myrank_esc\",\"type\": \"file\",\"path\": \"$fileinfo_esc\", \"name\": \"$pippo[filename]\",\"date\":\"$pippo[dateformatted]\", \"id\": \"$pippo[id]\",\"rpath\": \"$pippo[rpath]\",\"flags\": \"$pippo[flags]\",\"description\": \"$pippo[description]\",\"id_m\":\"$pippo[id_m]\", \"fax_type\":\"$pippo[fax_type]\", \"number\": \"$pippo[number]\", \"sender\": \"$pippo[name]\", \"device\":\"$pippo[device]\", \"filename\":\"$pippo[filename]\", \"sendto\":\"$pippo[sendto]\", \"msg\":\"$pippo[msg]\", \"com_id\":\"$pippo[com_id]\", \"date\":\"$pippo[dateformatted]\", \"pages\":\"$pippo[pages]\", \"duration\":\"$pippo[duration]\", \"quality\":\"$pippo[quality]\", \"rate\":\"$pippo[rate]\", \"data\":\"$pippo[data]\", \"errcorr\":\"$pippo[errcorr]\", \"page\":\"$pippo[page]\", \"resends\":\"$pippo[resends]\", \"resend_rcp\":\"$pippo[resend_rcp]\", \"forward_rcp\":\"$pippo[forward_rcp]\", \"letto\":\"$pippo[letto]\", \"doc_id\":\"$pippo[doc_id]\", \"tts\":\"$pippo[tts]\", \"ktime\":\"$pippo[ktime]\", \"rtime\":\"$pippo[rtime]\", \"job_id\":\"$pippo[job_id]\", \"state\":\"$pippo[state]\", \"user\":\"$pippo[user]\", \"attempts\":\"$pippo[attempts]\", \"esito\":\"$pippo[esito]\", \"tipo\":\"$pippo[tipo]\"");
		   $results ++;
	}
	if($results > 0) echo jsonReturn('advSearch');
}
function ResendError($path) {
	global $database;

	$query = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and deleted!='1';";
	$result = mysql_query($query,$database) ;
	$list = array();
	while($files = mysql_fetch_array($result, MYSQL_ASSOC)) array_push($list, $files);

	foreach ($list as $files){

			   if($files["state"]== '8' || $files["state"]== '99')  ResendFax($files["id"],"multiple");          
	}
	echo "{\"bindings\": [ { } ]}";  
}

function StopAll($path) {
        global $database;

        $query = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and deleted!='1';";
        $result = mysql_query($query,$database) ;
        $list = array();
        while($files = mysql_fetch_array($result, MYSQL_ASSOC)) array_push($list, $files);

        foreach ($list as $files){  stopSend($files["job_id"]); }
        echo "{\"bindings\": [ { } ]}";
}

function faxStat () {
	global $uploadDir,$database;
	$output='';
	jsonStart();

	system(''.$uploadDir.'/modem_status.pl>/tmp/faxstat.faxweb');
	$result="<table width=100% class=modemStat>";
	$status=true;
	$handle=fopen('/tmp/faxstat.faxweb','r+'); 
	while (!feof($handle)) {
			  $buffer = fgets($handle, 4096);
			  $buffer =  mysql_escape_string($buffer);
			  if ($status) {
				if (strstr($buffer,"HylaFAX")) $buffer = "<b>$buffer</b>"; 
				$result .= "<tr><td>";

				$buffer =  str_replace(":","</td><td>",$buffer);
				$buffer =  str_replace("Running and idle","<font color=green><b>Libero</b></font>",$buffer);
				$buffer =  str_replace("Waiting for modem to come free","<font color=orange><b>Reset Modem</b></font>",$buffer);
				$buffer =  str_replace("Waiting for modem to come ready","<font color=orange><b>Reset Modem</b></font>",$buffer);
				$buffer =  str_replace("Answering the phone","<font color=orange><b>Chiamata Risposta</b></font>",$buffer);
				$buffer =  str_replace("Initializing server","<font color=orange><b>In Preparazione</b></font>",$buffer);
				$buffer =  str_replace("Receiving facsimile","<font color=red><b>Ricezione Fax</b></font>",$buffer);
				$buffer =  str_replace("Receiving from","<font color=red><b>Ricezione da:</b></font>",$buffer);
				$buffer =  str_replace("Sending job","<font color=red><b>Invio Fax id:</b></font>",$buffer);
				$buffer =  str_replace("Running","<font color=green><b>In Esecuzione</b></font>",$buffer);
				$buffer =  str_replace("HylaFAX scheduler on","Server Fax",$buffer);

				if (strlen($buffer)<4) $result .= "</td><td></td></tr></table>";
					
				if (strstr($buffer,"JID")) {
					$status = false; 
					$result .= "<br><table width=100% class=modemStat>";
					$result .= "<tr><td>Id</td><td>S</td><td>Numero</td><td>Utente</td><td>Pagine</td><td>Chiamate</td><td>Invio</td><td>Stato</td></tr>";
				} else 
					$result .= "$buffer</td></tr>";
			  } else {
				$buffer =  str_replace("Busy signal detected","Occupato",$buffer);
				$buffer =  str_replace("No Carrier","Linea Assente",$buffer);
				$row=preg_split("/ +/",$buffer);
				$query = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where job_id='".$row[0]."';";
				$result2 = mysql_query($query,$database);
				$dati = mysql_fetch_assoc($result2);
				$result .= "<tr><td>$row[0]</td><td>$row[2]</td><td>$row[4]</td><td>$dati[user]</td><td>$row[5]</td><td>$row[6]</td><td>$row[7]</td><td>$row[8] $row[9] $row[10]</td></tr>";
			  }
	}
	$result .= "</table>";
	fclose($handle); 
	$output .= jsonAdd("\"stat\":\"$result\"");
	$output .= jsonReturn('faxStat');
		    
	echo $output;
}
function addRead ($id,$letto) {
	global $database;

	if($letto=='0') $query ="UPDATE ".$GLOBALS['tablePrefix']."fax set letto='".$_SESSION[name]."'  where id='".$id."';";
	else $query ="UPDATE ".$GLOBALS['tablePrefix']."fax set letto='0'  where id='".$id."';";
	$result = mysql_query($query,$database);
	echo "{\"bindings\": [ { } ]}";
}
function FaxLetto ($id) {
	global $database;

	$query ="UPDATE ".$GLOBALS['tablePrefix']."fax set letto='".$_SESSION[name]."' where id='".$id."';";
	$result = mysql_query($query,$database);
	echo "{\"bindings\": [ { } ]}";  
}
function ResendFax ($id,$type) {
	global $database,$dochome,$uploadDir,$notify;

	$today_ts = time();
	$today = date("y_m_d_G-i-s",$today_ts);
	$today2 = date("Y-m-d G:i:s",$today_ts);

	$query = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where id='".$id."';";
	$result = mysql_query($query,$database);
	$dati = mysql_fetch_assoc($result); 

	$doc=$dati["filename"];
	$up_pdf_filename->Value=$dati["filename"];
	$filetosend=$dochome.$dati["rpath"]."/".$doc;

	$nome_lista = "el_$today";
	$dest_array = array();

	$dest_array[$dati["number"]] = $dati["name"];

	$f=fopen($uploadDir.'/elenco_inviati/temp','w');
	$fd_temp_name=fopen($uploadDir.'/elenco_inviati/temp_name','w');

	for ($n = 0; $n <count($dest_array); $n++) {
			  $line = each ($dest_array);
			  fwrite($f,$line[key]."\n");
			  fwrite($fd_temp_name,$line[key].",".$line[value]."\n");
	}
	fclose($f);
	fclose($fd_temp_name);

        if($type=="single") { 
            		      $k_time= 'now + 3 hours';
		   	      $k_day =NULL;
			      $kill_date = rtrim("$k_time $k_day"); 
                              $OPT1=" -P normal";

        } else if ($type=="multiple") {
                              $k_time= 'now + 12 hours';
                              $k_day =NULL;
                              $kill_date = rtrim("$k_time $k_day");
                              $OPT1=" -P bulk";
        }

	#conto i numeri di telefono per impostare la priorità
	$lines=file($uploadDir.'/elenco_inviati/temp');

	$resends=$dati["resends"]+1;
	if($dati["state"]== '7')  $resend_rcp= $dati["user"]." ".$dati["date"]." OK<br>".$dati["resend_rcp"];
	else  $resend_rcp= $dati["user"]." ".$dati["date"]." KO<br>".$dati["resend_rcp"]; 
	

	if($notify)
	  $R = " -R ";
  	else
	  $R = " ";
	system('sendfax -m '.$R.' -f '.$_SESSION[name].' -o '.$_SESSION[name].' -k "'.$kill_date.'" -n '.$OPT1.' -z '.$uploadDir.'/elenco_inviati/temp \''.$filetosend.'\'> '.$uploadDir.'/elenco_inviati/listajob_'.$today.'');

	$elenco = fopen($uploadDir."/elenco_inviati/temp", "r");
	$elenco_nomi = fopen($uploadDir."/elenco_inviati/temp_name", "r");
	$joblist = fopen($uploadDir."/elenco_inviati/listajob_".$today, "r");
	while (!feof($elenco)) {
			$tel = rtrim(fgets($elenco),256);
			$row_name = rtrim(fgets($elenco_nomi),256);
			$tmp_row = preg_split("/,/", $row_name);
			$jlist = preg_split("/[\s,]+/", rtrim(fgets($joblist),256));
			$job_buf = $jlist[3];
			if ($tel!= NULL){

		         logAction('resendFax',$id);
			 $query4 ="UPDATE ".$GLOBALS['tablePrefix']."fax set job_id='".$job_buf."', user='".$_SESSION[name]."', date='".$today2."', resends='".$resends."', resend_rcp='".$resend_rcp."', state='', tipo='W', esito='In Corso ...' where id='".$id."';";
			 $result4 = mysql_query($query4,$database);

			 }
	}
	fclose($elenco);
	fclose($elenco_nomi);
	fclose($joblist);
	#salvo la lista di destinatari finale
	system('cp '.$uploadDir.'/elenco_inviati/temp_name '.$uploadDir.'/elenco_inviati/'.$nome_lista.'');
	#cancello la lista di jobs dopo averla utilizzata
	exec('rm -f '.$uploadDir.'/faxweb/elenco_inviati/listajob_'.$today.'');
	$up_pdf_filename->Value=NULL;
	$filetosend='';
	echo "{\"bindings\": [ { } ]}";  
}
function addAddress ($fax,$address) {
	global $database;

	if($fax!='' && $address!='') {

		   $query = "SELECT * FROM ".$GLOBALS['tablePrefix']."association where fax='".$fax."';";
		   $result = mysql_query($query,$database);
		   $dati = mysql_fetch_assoc($result); 

		   if($dati["FAX"]!=NULL) {
		      
			$query2 = "UPDATE ".$GLOBALS['tablePrefix']."association SET fax='".$fax."', nome='".$address."' WHERE fax='".$fax."';";
			$result2 = mysql_query($query2,$database);

		   } else {
		      
			$query3 = "INSERT INTO ".$GLOBALS['tablePrefix']."association SET fax='".$fax."', nome='".$address."';";
			$result3 = mysql_query($query3,$database);

		   }
		   $query4 = "UPDATE ".$GLOBALS['tablePrefix']."fax SET name='".$address."' WHERE number='".$fax."';";
		   $result4 = mysql_query($query4,$database);

		   echo "{\"bindings\": [ { } ]}";  
	} else {
		error('Nome o Numero Fax Vuoti.');
	} 
}
function stopSend ($job_id) {

	global $uploadDir;
	if(isset($job_id)) {
		         logAction('stopSendFax',$job_id);
			 exec (''.$uploadDir.'/rmfax.pl '.$_SESSION[name].' '.$job_id);
			 sleep(2);
			 echo "{\"bindings\": [ { } ]}";  
	} else {
		 error('Operazione Non Riuscita');
	} 
}
function sendMail ($id,$mail,$note) {
	global $database;
	$today_ts = time();
	$today = date("Y/m/d G:i:s",$today_ts);
	$test= preg_split('/;/', $mail);

	for($i=0; $i < count($test); $i++) {
		      $txt=0;
		      $test[$i] = trim($test[$i]);  
		      if(!$test[$i]) {  
		      $txt=1;
		      }  
		      $num_at = count(explode( '@', $test[$i] )) - 1;  
		      if($num_at != 1) {  
		      $txt=1;  
		      }  
		      if(strpos($test[$i],';') || strpos($test[$i],',') || strpos($test[$i],' ')) {  
		      $txt=1;  
		      }  
		      if(!preg_match( '/^[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,63}$/', $test[$i])) {  
		      $txt=1;  
		      }  
		      if($txt==0){
		      $email_to= $email_to.$test[$i].",";  
		      $recipient= $recipient.$test[$i]." (".$today.")<br>"; }
	}
	$query = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where id='".$id."';";
	$result = mysql_query($query,$database);
	$file = mysql_fetch_assoc($result);

	$email_from='faxmaster';
	if($file["name"]==NULL) {
			      $email_subject='Fax Ricevuto da: '.$file["number"].' il '.$file["date"];
			      $email_message='Fax Ricevuto da : '.$file["number"].'<br>';
	} else { 
		      $email_subject='Fax Ricevuto da: '.$file["name"].' il '.$file["date"];
		      $email_message='Fax Ricevuto da : '.$file["name"].'<br>';
	}
	$email_message.='in data : '.$file["date"].'<br>';
	$email_message.='Pagine : '.$file["pages"].'<br><br>';
	$email_message.='Fax Inoltrato da : '.$_SESSION[name].'<br><br>';
	$email_message.='Note : '.$note;
	$send_app = fopen($file["path"]."/".$file["filename"],'rb');
	$data = fread($send_app,filesize($file["path"]."/".$file["filename"]));
	fclose($send_app);

	$headers = "From: ".$email_from;

	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

	$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
	$email_message = "This is a multi-part message in MIME format.\n\n" .  "--{$mime_boundary}\n" . "Content-Type:text/html; charset=\"iso-8859-1\"\n" .  "Content-Transfer-Encoding: 7bit\n\n" .  $email_message . "\n\n";

	$data = chunk_split(base64_encode($data),76,"\n");

	$email_message .= "--{$mime_boundary}\n" .  "Content-Type: image/tiff;\n" . " name=\"".$file["filename"]."\"\n" .  "Content-Disposition: attachment;\n" . " filename=\"".$file["filename"]."\"\n" .  "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n" ."--{$mime_boundary}--\n";
	$sending_ok = @mail($email_to, $email_subject, $email_message, $headers);

	$old = $file["forward_rcp"];

	if ($old <> NULL)  {

			      $query3 = "UPDATE ".$GLOBALS['tablePrefix']."fax SET forward_rcp='".$recipient.$old."' WHERE id='".$file["id"]."';";
	} else {
		      $query3 = "UPDATE ".$GLOBALS['tablePrefix']."fax SET forward_rcp='".$recipient."' WHERE id='".$file["id"]."';";
	}

	$result3 = mysql_query($query3,$database);
	logAction('sendMail',$file["id"]);
				
	if($sending_ok) {
			echo "{\"bindings\": [ { } ]}";  
	} else {
			error('Errore nell\'invio mail.');
	}
}
function getForward ($type) {
	$output = '';
	jsonStart();
	$ldapusers = array();


	$ds=ldap_connect("localhost");
	$r=ldap_bind($ds);
	$sr=ldap_search($ds, $GLOBALS[base_ldap], "cn=*");
	$info = ldap_get_entries($ds, $sr);
	for ($i=0; $i<$info["count"]; $i++) {
					       $user=$info[$i]['cn'][0];
					       $mail=$info[$i]['mail'][0];
					       $oclass=$info[$i]['objectclass'][0];
					       if ($oclass=='posixGroup') $user = " Grp: ".$user;
					       if ($type == 'user' && $oclass!='posixGroup') array_push($ldapusers,array("$user","$mail"));
					       elseif ($type == 'group' && $oclass=='posixGroup') array_push($ldapusers,array("$user","$mail"));
					       elseif ($type == 'all') array_push($ldapusers,array("$user","$mail"));
					     }
	ldap_close($ds);
	sort($ldapusers);
	foreach ($ldapusers as $ldapuser) { 
                                             $ldapuser0_esc= escape($ldapuser[0]);
                                             $ldapuser1_esc= escape($ldapuser[1]);
					     $output .=  jsonAdd("\"name\":\"$ldapuser0_esc\",\"address\":\"$ldapuser1_esc\"");
	   	 			  }
	$output .=  jsonReturn('getForward');
	echo $output;    
}
function search($terms,$path){
	global $database,$dateFormat,$fileinfo,$limit;
	jsonStart();
        $where= getPermissions();
	$terms = mysql_escape_string($terms);
	$query3=" select COUNT(*) as tot from $GLOBALS[tablePrefix]fax where (match(name,number,description) against(\"$terms\") or (name like \"%$terms%\" or number like \"%$terms%\" or description like \"%$terms%\" or date like \"%$terms%\")) and path='$path' and deleted != '1' $where;";
	$result3 = mysql_query($query3,$database);
	$tot = mysql_result($result3,0);

	$query = "select *,date_format(`date`,\"$dateFormat\") as `dateformatted`, match(name,number,description) against(\"$terms\") as `rank` from $GLOBALS[tablePrefix]fax where (match(name,number,description) against(\"$terms\") or (name like \"%$terms%\" or number like \"%$terms%\" or description like \"%$terms%\" or date like \"%$terms%\")) and path='$path' and deleted != '1' $where order by rank,date desc limit $limit;";
	if($tot>=$limit) $view=$limit;
	else if ($tot<$limit) $view=$tot;
	$result = mysql_query($query,$database);
	$result2 = array();
	while($files = mysql_fetch_assoc($result)) array_push($result2, $files);

	$result2 = array_reverse($result2, false);

	$toprank = 0.000001;
	for ($i = 0; $i < count($result2); $i++) {
		$files = $result2[$i];
		if($toprank == 0.000001 and $files['rank'] != 0)$toprank = $files['rank'];
		$myrank = round(($files['rank']/$toprank)*3)+2;
		getFileInfo($files['id']);

                $tot_esc= escape($tot);
                $view_esc= escape($view);
                $myrank_esc= escape($myrank);
                $fileinfo_esc= escape($fileinfo[virtualpath]);
                $pippo = array();
                foreach ($files as $key => $value) { $pippo[$key] = escape($value); }

		jsonAdd("\"tot\":\"$tot_esc\",\"view\":\"$view_esc\",\"rank\":\"$myrank_esc\",\"type\": \"file\",\"path\": \"$fileinfo_esc\", \"name\": \"$pippo[filename]\",\"date\":\"$pippo[dateformatted]\", \"id\": \"$pippo[id]\",\"rpath\": \"$pippo[rpath]\",\"flags\": \"$pippo[flags]\",\"description\": \"$pippo[description]\",\"id_m\":\"$pippo[id_m]\", \"fax_type\":\"$pippo[fax_type]\", \"number\": \"$pippo[number]\", \"sender\": \"$pippo[name]\", \"device\":\"$pippo[device]\", \"filename\":\"$pippo[filename]\", \"sendto\":\"$pippo[sendto]\", \"msg\":\"$pippo[msg]\", \"com_id\":\"$pippo[com_id]\", \"date\":\"$pippo[dateformatted]\", \"pages\":\"$pippo[pages]\", \"duration\":\"$pippo[duration]\", \"quality\":\"$pippo[quality]\", \"rate\":\"$pippo[rate]\", \"data\":\"$pippo[data]\", \"errcorr\":\"$pippo[errcorr]\", \"page\":\"$pippo[page]\", \"resends\":\"$pippo[resends]\", \"resend_rcp\":\"$pippo[resend_rcp]\", \"forward_rcp\":\"$pippo[forward_rcp]\", \"letto\":\"$pippo[letto]\", \"doc_id\":\"$pippo[doc_id]\", \"tts\":\"$pippo[tts]\", \"ktime\":\"$pippo[ktime]\", \"rtime\":\"$pippo[rtime]\", \"job_id\":\"$pippo[job_id]\", \"state\":\"$pippo[state]\", \"user\":\"$pippo[user]\", \"attempts\":\"$pippo[attempts]\", \"esito\":\"$pippo[esito]\", \"tipo\":\"$pippo[tipo]\"");
		$results ++;
	}
	if($results > 0)
		echo jsonReturn('search');
}
function getFile($fileid){
	global $database,$filepath,$fileinfo;
	if(getFileInfo($fileid)){
		        logAction('getFile',$fileid);
			$query = "update $GLOBALS[tablePrefix]fax set downloads=downloads+1 where id='$fileid';";
			$result = mysql_query($query,$database);
			header("Pragma: public"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); 
			header("Content-type: $fileinfo[type]");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: ".filesize($filepath));
			header("Content-disposition: attachment; filename=\"".basename($filepath)."\"");
			readfile("$filepath");
	}else{
		error ('access denied');
	}
}
function emailFilePackage($fileids,$to,$from,$message){
	global $fileinfo,$filepath,$database;
	
	$fileids = preg_split("/\,/",$fileids);
	
	$boundary = "DU_" . md5(uniqid(time()));	
	$headers = "From: $from". "\r\n";
	$headers .= "MIME-Version: 1.0"."\r\n";
	$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\";". "\r\n";
	$mailMessage = "--$boundary Content-Type: text/plain; charset=\"iso-8859-1\" Content-Transfer-Encoding: 7bit $message ";

	foreach($fileids as $fileid){
		if(getFileInfo($fileid)){
									
		   $query = "update $GLOBALS[tablePrefix]fax set downloads=downloads+1 where id='$fileid';";
		   $result = mysql_query($query,$database);
			
		   $ct = $fileinfo['type'];
		   if($ct=='')$ct = 'application/force-download';
		   $mailMessage.= "--$boundary\nContent-Type: $ct\nContent-Transfer-Encoding: base64\nContent-Disposition: attachment; filename=\"$fileinfo[filename]\"\n\n";
		   $mailMessage.= chunk_split(base64_encode(file_get_contents($filepath)));
		}
	}
	$mailMessage.= "\n--$boundary--";
	ini_set(SMTP,'mvs5.duarte.com');
	
	if(mail($to,"File from $from",$mailMessage,$headers)) $status = "Message Sent";
	else $status = "ERROR: Message Not Sent";
	
	jsonStart();
	jsonAdd("\"status\": \"$status\"");
	echo jsonReturn("bindings"); 
}
function getFilePackage($fileids,$returnContent = false){
	global $database,$fileinfo,$filepath;
	
	$fileids = preg_split("/\,/",$fileids);
	include_once("inc/createZip.inc.php");
	$createZip = new createZip;
	$fileCount = 0;
	foreach($fileids as $fileid){
		if(getFileInfo($fileid)){
				$query = "update $GLOBALS[tablePrefix]fax set downloads=downloads+1 where id='$fileid';";
				$result = mysql_query($query,$database);
			
				$createZip -> addFile(file_get_contents($filepath), "$fileinfo[filename]");
				$fileCount++;
	}
               }
		
	if($fileCount > 0){
		if($returnContent != true){
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=\"package.zip\"");
			echo $createZip -> getZippedfile();
		}else{
			return $createZip->getZippedfile();
		}
	}else{
		error('no files zipped');
	}
}
function getPermissions () { 
	global $faxweb,$device;
	if ($faxweb['filterdevice']!=true && $faxweb['filterrcv']!=true && $faxweb['filtersent']==true) {

		   foreach($faxweb['adminsent'] as $adminsent)
		    {
		     if($_SESSION[name]==$adminsent) $sent= true;
		    }

		   if ($sent!=true)  $where="and (($GLOBALS[tablePrefix]fax.fax_type='R') OR ($GLOBALS[tablePrefix]fax.fax_type='I' and user='$_SESSION[name]'))";

	} else if ($faxweb['filterdevice']!=true && $faxweb['filterrcv']==true && $faxweb['filtersent']!=true) {

		   foreach($faxweb['adminrcv'] as $adminrcv)
		    {
		     if($_SESSION[name]==$adminrcv)  $rcv= true;
		    }

		   if ($rcv!=true) $where="and ($GLOBALS[tablePrefix]fax.fax_type='I')";

	} else if ($faxweb['filterdevice']!=true && $faxweb['filterrcv']==true && $faxweb['filtersent']==true) {

		   foreach($faxweb['adminsent'] as $adminsent)
		    {
		     if($_SESSION[name]==$adminsent) $sent= true;
		    }

		   foreach($faxweb['adminrcv'] as $adminrcv)
		    {
		     if($_SESSION[name]==$adminrcv)  $rcv= true;
		    }

		   if ($sent!=true && $rcv!=true)  $where="and ($GLOBALS[tablePrefix]fax.fax_type='I' and user='$_SESSION[name]')";

		   if ($sent==true && $rcv!=true ) $where="and ($GLOBALS[tablePrefix]fax.fax_type='I')";

		   if ($sent!=true && $rcv==true ) $where="and (($GLOBALS[tablePrefix]fax.fax_type='R') OR ($GLOBALS[tablePrefix]fax.fax_type='I' and user='$_SESSION[name]'))";

	} else if ($faxweb['filterdevice']==true && $faxweb['filterrcv']!=true && $faxweb['filtersent']!=true) {

		   foreach($device as $key => $value)
		    {
		     foreach($device[$key] as $user_device)
		      {
		       if($user_device==$_SESSION[name]) {

			  $n= $n+1;
			 $fax[$n]=$key;
			}
		      }
		    }

		   $where= "and (($GLOBALS[tablePrefix]fax.fax_type='I')";
		   for($i=$n; $i > 0 ; $i--) {

		       $where=$where." OR ($GLOBALS[tablePrefix]fax.fax_type='R' and device='".$fax[$i]."')";
		    }
		   $where=$where.")";

	} else if ($faxweb['filterdevice']==true && $faxweb['filterrcv']!=true && $faxweb['filtersent']==true) {

		   foreach($faxweb['adminsent'] as $adminsent)
		    {
		     if($_SESSION[name]==$adminsent) $sent= true;
		    }
		   if ($sent!=true)  $where="and (($GLOBALS[tablePrefix]fax.fax_type='I' and user='$_SESSION[name]')";
		   else $where= "and (($GLOBALS[tablePrefix]fax.fax_type='I')";

		   foreach($device as $key => $value)
		    {
		     foreach($device[$key] as $user_device)
		      {
		       if($user_device==$_SESSION[name]) {

			  $n= $n+1;
			  $fax[$n]=$key;
			}
		      }
		    }
		   for($i=$n; $i > 0 ; $i--) {

		       $where=$where." OR ($GLOBALS[tablePrefix]fax.fax_type='R' and device='".$fax[$i]."')";

		    }
		   $where=$where.")";

	} else if ($faxweb['filterdevice']==true && $faxweb['filterrcv']==true && $faxweb['filtersent']!=true) {

		   foreach($faxweb['adminrcv'] as $adminrcv)
		    {
		     if($_SESSION[name]==$adminrcv)  $rcv= true;
		    }

		   if ($rcv!=true) $where="and ($GLOBALS[tablePrefix]fax.fax_type='I')";
		   else {
			 foreach($device as $key => $value)
			  {
			   foreach($device[$key] as $user_device)
			    {
			     if($user_device==$_SESSION[name]) {

				$n= $n+1;
				$fax[$n]=$key;
			      }
			    }
			  }

			 $where= "and (($GLOBALS[tablePrefix]fax.fax_type='I')";
			 for($i=$n; $i > 0 ; $i--) {
 
			     $where=$where." OR ($GLOBALS[tablePrefix]fax.fax_type='R' and device='".$fax[$i]."')";
			  }
			 $where=$where.")";
		       } 

	} else if ($faxweb['filterdevice']==true && $faxweb['filterrcv']==true && $faxweb['filtersent']==true)  {

		   foreach($faxweb['adminsent'] as $adminsent)
		    {
		     if($_SESSION[name]==$adminsent) $sent= true;
		    }

		   foreach($faxweb['adminrcv'] as $adminrcv)
		    {
		     if($_SESSION[name]==$adminrcv)  $rcv= true;
		    }

		   if ($sent!=true && $rcv!=true)  $where="and ($GLOBALS[tablePrefix]fax.fax_type='I' and user='$_SESSION[name]')";

		   else if ($sent==true && $rcv!=true ) $where="and ($GLOBALS[tablePrefix]fax.fax_type='I')";

		   else if ($sent!=true && $rcv==true ) {

			foreach($device as $key => $value)
			 {
			  foreach($device[$key] as $user_device)
			   {
			    if($user_device==$_SESSION[name]) {

			       $n= $n+1;
			       $fax[$n]=$key;
			     }
			   }
			 }

			$where= "and (($GLOBALS[tablePrefix]fax.fax_type='I' and user='$_SESSION[name]')";
			for($i=$n; $i > 0 ; $i--) {

			    $where=$where." OR ($GLOBALS[tablePrefix]fax.fax_type='R' and device='".$fax[$i]."')";
			 }
			$where=$where.")";

		   } else if ($sent==true && $rcv==true ) {

		       foreach($device as $key => $value)
			{
			 foreach($device[$key] as $user_device)
			  {
			   if($user_device==$_SESSION[name]) {

			      $n= $n+1;
			      $fax[$n]=$key;
			    }
			  }
			}

		       $where= "and (($GLOBALS[tablePrefix]fax.fax_type='I')";
		       for($i=$n; $i > 0 ; $i--) {

			   $where=$where." OR ($GLOBALS[tablePrefix]fax.fax_type='R' and device='".$fax[$i]."')";
			}
		      $where=$where.")";
		   }
	}
        return $where;

}
function getFolder($path,$page){
	global $database,$resource,$dateFormat,$faxweb,$limit,$device,$dochome;
	userPermissions();
	$output = '';
	jsonStart();
	$path = mysql_escape_string($path);


	// For Virtual Directories
	if($path == '' || $path == '/'){
$output .=  jsonAdd("\"displayname\":\"FaxWeb\",\"scheme\":\"admin\",\"type\": \"directory\", \"name\": \"docs\", \"path\": \"$dochome\",\"virtual\":\"true\"");
		$output .= jsonReturn('getFolder');
	}
	if($output > ''){
		if($resource != true){
			echo $output;
			die;
		}else{
			return $output;
		}
	}	
	// Non Virtual Directories
	$fullpath = getUserPath($path).$path;

	databaseSync($fullpath,$path);

	if (is_dir($fullpath)) {
			if ($dh = opendir($fullpath)) {
			   $file= array();
			   while (($files = readdir($dh)) !== false) array_push($file, $files);

			   reset($file);
			   sort($file);
			   reset($file);
                           if($fullpath==$dochome.'/sentm') $file=array_reverse($file, false);

			   for ($i = 0; $i < count($file); $i++) {

			     if($file[$i] != '.' && $file[$i] != '..' && is_dir($fullpath . '/' . $file[$i]) ){

                               $file[$i]= escape($file[$i]) ;
			       jsonAdd("\"type\": \"directory\", \"name\": \"$file[$i]\", \"path\": \"$path/$file[$i]\"");
			      }
			    }
			   closedir($dh);
			}
	}else {
		      error("directory doesnt exist $fullpath");
	}

        $where= getPermissions();

        $newlimit= $limit * ( $page - 1 );

	$query = "select $GLOBALS[tablePrefix]fax.fax_type,         $GLOBALS[tablePrefix]fax.name,               $GLOBALS[tablePrefix]fax.device,
			 $GLOBALS[tablePrefix]fax.sendto,           $GLOBALS[tablePrefix]fax.number,             $GLOBALS[tablePrefix]fax.msg,
			 $GLOBALS[tablePrefix]fax.com_id,           date_format($GLOBALS[tablePrefix]fax.date,\"$dateFormat\") as dateformatted,
			 $GLOBALS[tablePrefix]fax.pages,            $GLOBALS[tablePrefix]fax.duration,           $GLOBALS[tablePrefix]fax.quality,
			 $GLOBALS[tablePrefix]fax.rate,             $GLOBALS[tablePrefix]fax.data,               $GLOBALS[tablePrefix]fax.errcorr,
			 $GLOBALS[tablePrefix]fax.page,             $GLOBALS[tablePrefix]fax.resends,            $GLOBALS[tablePrefix]fax.resend_rcp,
			 $GLOBALS[tablePrefix]fax.forward_rcp,      $GLOBALS[tablePrefix]fax.letto,              $GLOBALS[tablePrefix]fax.doc_id,             
			 $GLOBALS[tablePrefix]fax.tts,              $GLOBALS[tablePrefix]fax.ktime,              $GLOBALS[tablePrefix]fax.rtime,
			 $GLOBALS[tablePrefix]fax.job_id,           $GLOBALS[tablePrefix]fax.state,              $GLOBALS[tablePrefix]fax.user,
			 $GLOBALS[tablePrefix]fax.attempts,         $GLOBALS[tablePrefix]fax.esito,              $GLOBALS[tablePrefix]fax.tipo,
			 $GLOBALS[tablePrefix]fax.id,               $GLOBALS[tablePrefix]fax.filename,           $GLOBALS[tablePrefix]fax.path,
			 $GLOBALS[tablePrefix]fax.rpath,            $GLOBALS[tablePrefix]fax.type,               $GLOBALS[tablePrefix]fax.downloads,
			 $GLOBALS[tablePrefix]fax.status,           $GLOBALS[tablePrefix]fax.flags,              $GLOBALS[tablePrefix]fax.description,
			 $GLOBALS[tablePrefix]fax.thumb,            $GLOBALS[tablePrefix]fax.thumbC,             $GLOBALS[tablePrefix]fax.id_m
		  from $GLOBALS[tablePrefix]fax where (path='$fullpath' and deleted!='1') $where order by $GLOBALS[tablePrefix]fax.date desc limit $newlimit,$limit;";
	
	$result = mysql_query($query,$database);
	$result2 = array();              
	while($files = mysql_fetch_assoc($result)) array_push($result2, $files);

	$result2 = array_reverse($result2, false);

	for ($i = 0; $i < count($result2); $i++) {
		$files = $result2[$i];
                $pippo = array();
                foreach ($files as $key => $value) { $pippo[$key] = escape($value); }

                $output .= jsonAdd("\"type\": \"file\", \"name\": \"$pippo[filename]\",\"date\":\"$pippo[dateformatted]\", \"id\": \"$pippo[id]\",\"rpath\": \"$pippo[rpath]\",\"flags\": \"$pippo[flags]\",\"description\": \"$pippo[description]\",\"id_m\":\"$pippo[id_m]\", \"fax_type\":\"$pippo[fax_type]\", \"number\": \"$pippo[number]\", \"sender\": \"$pippo[name]\", \"device\":\"$pippo[device]\", \"filename\":\"$pippo[filename]\", \"sendto\":\"$pippo[sendto]\", \"msg\":\"$pippo[msg]\", \"com_id\":\"$pippo[com_id]\", \"date\":\"$pippo[dateformatted]\", \"pages\":\"$pippo[pages]\", \"duration\":\"$pippo[duration]\", \"quality\":\"$pippo[quality]\", \"rate\":\"$pippo[rate]\", \"data\":\"$pippo[data]\", \"errcorr\":\"$pippo[errcorr]\", \"page\":\"$pippo[page]\", \"resends\":\"$pippo[resends]\", \"resend_rcp\":\"$pippo[resend_rcp]\", \"forward_rcp\":\"$pippo[forward_rcp]\", \"letto\":\"$pippo[letto]\", \"doc_id\":\"$pippo[doc_id]\", \"tts\":\"$pippo[tts]\", \"ktime\":\"$pippo[ktime]\", \"rtime\":\"$pippo[rtime]\", \"job_id\":\"$pippo[job_id]\", \"state\":\"$pippo[state]\", \"user\":\"$pippo[user]\", \"attempts\":\"$pippo[attempts]\", \"esito\":\"$pippo[esito]\", \"tipo\":\"$pippo[tipo]\"");
	}
	$output .= jsonReturn('getFolder');
	      
	//global $dbgh;
	//fwrite($dbgh, "__RESULT__\n$output\n");

	if($resource != true) echo $output;
	else return $output;
}
function getFolderMeta($path){
	global $database,$dochome,$uploadDir;
    	jsonStart();
    	$path = mysql_escape_string($path);
    	$fullpath = getUserPath($path).$path;
    	$size = filesize_format(get_size($fullpath));
    	$name = basename($fullpath);
    	$modified = '';
    	$created ='';  
 
        $where= getPermissions();
    	$query2 = "SELECT count(*) AS NUM FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and deleted!='1' $where;";
    	$result2 = mysql_query($query2,$database);
    	$num = mysql_result($result2,0);

    	if (preg_match("/sent/", $path)) { 
    				$query = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and deleted!='1' $where;";
    				$result = mysql_query($query,$database);
    				$list = array();
    				while($line = mysql_fetch_array($result, MYSQL_ASSOC)) array_push($list, $line);

    				$S7=0;  // INVIATI
    				$S8=0;  // ERRORI
    				$SOTHER=0;  // RITRASMESSI
    				$SNULL=0;  // IN ATTESA

    				foreach($list as $dest_line) {
	   					if ($dest_line[state]==7) $S7++;
	   					elseif ($dest_line[state]==8) $S8++;
	   					elseif ($dest_line[state]==NULL) $SNULL++;
	   					else $SOTHER++;
	   			}
    	}
        if (preg_match("/sentm\//", $path)) {
    				$query3 = "SELECT id_m FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and deleted!='1' limit 1;";
			    	$result3 = mysql_query($query3,$database);
			    	$id_m = @mysql_result($result3,0);

			    	$query4 = "SELECT descrizione FROM ".$GLOBALS['tablePrefix']."documents where id_m='".$id_m."';";
			    	$result4 = mysql_query($query4,$database); 
			    	$descrizione = @mysql_result($result4,0);
    	}
        $name_esc= escape($name);
        $size_esc= escape($size);
        $num_esc= escape($num);
        $S7_esc= escape($S7);
        $S8_esc= escape($S8);
        $SOTHER_esc= escape($SOTHER);
        $SNULL_esc= escape($SNULL);
        $descrizione_esc= escape($descrizione);
    	jsonAdd("\"name\": \"$name_esc\", \"size\": \"$size_esc\", \"numero\": \"$num_esc\", \"inviati\": \"$S7_esc\", \"errori\": \"$S8_esc\", \"ritrasmessi\": \"$SOTHER_esc\", \"attesa\": \"$SNULL_esc\",\"desc\": \"$descrizione_esc\"");
    	echo jsonReturn('getFolderMeta');
}
function getMeta($fileid){
	global $fileinfo;

	if(getFileInfo($fileid)){
			jsonStart();
			if(getUserAuth('metaEdit',$fileinfo['virtualpath'])) {
				jsonAdd("\"edit\": \"true\"");
			} else {
				jsonAdd("\"edit\": \"false\"");
			}
		    
			if($fileinfo['type'] > '') $type = $fileinfo['type'];
			else $type = "document";
                        $filename_esc    = escape($fileinfo[filename]);
                        $virtualpath_esc = escape($fileinfo[virtualpath]);
                        $image_esc       = escape($fileinfo[image]);
                        $type_esc        = escape($type);
                        $date_esc        = escape($fileinfo['date']);
                        $downloads_esc   = escape($fileinfo[downloads]);
                        $description_esc = escape($fileinfo[description]);
                        $flags_esc       = escape($fileinfo[flags]);
                        $filetype_esc    = escape($fileinfo[type]);
                        $size_esc        = escape($fileinfo[size]);


			jsonAdd("\"filename\": \"$filename_esc\",\"path\": \"$virtualpath_esc\",\"image\":$image_esc,\"type\": \"$type_esc\", \"date\": \"$date_esc\", \"downloads\": \"$downloads_esc\", \"description\": \"$description_esc\", \"flags\": \"$flags_esc\", \"type\": \"$filetype_esc\", \"size\": \"$size_esc\"");
			if($type == "image/jpeg"){
				      if(function_exists("exif_read_data")){
					      $exif = exif_read_data($fileinfo['path'].'/'.$fileinfo['filename']);
				      }
			}
	} else {
	  error('access denied');
	}
	echo jsonReturn('getMeta');
}
function setMeta($fileid,$description,$flags){
	global $database,$fileinfo;

 	$fileid = mysql_escape_string($fileid);
  	$description = mysql_escape_string($description);
  	$flags = mysql_escape_string($flags);

  	if(getFileInfo($fileid)){

      				$query = "update $GLOBALS[tablePrefix]fax set description=\"$description\",flags=\"$flags\" where id='$fileid';";
      				$result = mysql_query($query,$database);
                                echo "{\"bindings\": [ { } ]}";

  	} else {error('access denied');}
}
function fileRename($fileid,$filename){
	global $database,$fileinfo;

  	$fileid = mysql_escape_string($fileid);
	$filename = mysql_escape_string($filename);
  	$filename = str_replace("\\","",$filename);
  	$filename = str_replace("/","",$filename);

  	if(getFileInfo($fileid)){
	  			logAction('fileRename',$fileid);
				$query = "update $GLOBALS[tablePrefix]fax set filename=\"$filename\" where id='$fileid';";
	 			$result = mysql_query($query,$database);
	  			rename($fileinfo['path'].'/'.$fileinfo['filename'],$fileinfo['path'].'/'.$filename);
  	} else {
    		error('rename denied');
  	}
}
function fileDelete($fileid){
	global $database,$fileinfo,$deleted;

  	$fileid = mysql_escape_string($fileid);

  	if(getFileInfo($fileid)){
	  		logAction('fileDelete',$fileid);
	  		$query3 = "UPDATE ".$GLOBALS['tablePrefix']."fax SET deleted='1' WHERE id='".$fileid."';";
	  		$result3 = mysql_query($query3,$database);
                        echo "{\"bindings\": [ { } ]}";
  	} else {
    		error('access denied');
  	}
}
function fileMove($fileid,$path){
	global $database,$fileinfo;
      
	$fileid = mysql_escape_string($fileid);
	
	$path = str_replace("//","/",$path);
	$path = str_replace("..","",$path);
	
	$path = mysql_escape_string($path);

	$FilePath=getFilePath($fileid);
	$path_source=preg_split('/\//',$path);
	$path_destination=preg_split('/\//',$FilePath);

	if(getFileInfo($fileid) && $path_destination[5]==$path_source[5]){
	    $newPath = getUserPath($path).$path;
	    if(is_dir($newPath)){
		  logAction('moveFile',$fileid);
		  $query = "update $GLOBALS[tablePrefix]fax set path=\"$newPath\",rpath=\"".substr($newPath,22)."\" where id=$fileid;";
		  $result = mysql_query($query,$database);
		  rename($fileinfo['path'].'/'.$fileinfo['filename'],$newPath.'/'.$fileinfo['filename']);
                  echo "{\"bindings\": [ { } ]}";
	     }else{
	          error('La nuova directory non esiste.');
	     }
        }else{
	  error('Impossibile spostare il file.');
	} 
}
function getFilePath($fileid){
        global $database,$filepath,$fileinfo,$imageTypes;

        $fileid=mysql_escape_string($fileid);
        $query = "select * from $GLOBALS[tablePrefix]fax where id=$fileid";
        $result = mysql_query($query,$database);
        if(mysql_num_rows($result) == 0){
                error('bad fileid');
        }
        $file = mysql_fetch_assoc($result);

        $filePath = $file['path'];
       
        return($filePath);
}
function folderRename($path,$name,$newname){
	global $database,$dochome;

  	$newname = mysql_escape_string($newname);
  	$name = mysql_escape_string($name);
  	$path = mysql_escape_string($path);

    	$currentPath = getUserPath($path).$path.'/'.$name;
    	$newPath = getUserPath($path).$path.'/'.$newname;
    
    	if(is_dir($currentPath) && !is_dir($newPath) && $currentPath!=$dochome.'/Ricevuti' && $currentPath!=$dochome.'/Inviati' && $currentPath!=$dochome.'/Invii Multipli'){
	  		logAction('folderRename',$newPath);
	  		if(rename($currentPath,$newPath)){
	    				$query = "update $GLOBALS[tablePrefix]fax set path=\"$newPath\",rpath=\"".substr($newPath,22)."\" where path=\"$currentPath\"";
	    				$result = mysql_query($query,$database);
            				echo "{\"bindings\": [ { newpath: \"$newPath\", name: \"$newname\" } ]}";
	  		} else {
            				error('Impossibile rinominare la cartella.');
	  		}
	} else {
	  error('Impossibile rinominare la cartella: nome non corretto.');
	}
}
function folderMove($name,$path,$newpath){
	global $database;
      
	$name = mysql_escape_string($name);
	$path = mysql_escape_string($path);

	$newpath = str_replace("..","",$newpath);
 	$newpath = mysql_escape_string($newpath);     
      
        $path_source=preg_split('/\//',$path);
        $path_destination=preg_split('/\//',$newpath);

	if($path_source[5]==$path_destination[5] && $path_destination[5]!=NULL){
      
	  $userPath = getUserPath($path).$path.'/'.$name;
	  $userNewPath = getUserPath($newpath).$newpath.'/'.$name;
      
	  if(is_dir($userPath) && !is_dir($userNewPath)){
		logAction('folderMove',$userNewPath);
		if(rename($userPath,$userNewPath)){
		  $query = "update $GLOBALS[tablePrefix]fax set path=\"$userNewPath\",rpath=\"".substr($newpath,22)."/$name\" where path=\"$userPath\"";
		  $result = mysql_query($query,$database);
                  echo "{\"bindings\": [ { } ]}";
		}else{
                  echo "{\"bindings\": [ { error } ]}";
		}
      
	      }else{
		error('old name doesnt exist or new name already exists');
	      }
      
	}else{
	  error('Impossibile spostare la cartella.');
	}
}
function folderDelete($folder){
	global $database;

	$folder = mysql_escape_string($folder);

		$deleteDir = getUserPath($folder).$folder;
		logAction('folderDelete',$deleteDir);
	
		if(deleteDir($deleteDir)){
                        $query = "UPDATE ".$GLOBALS['tablePrefix']."fax SET deleted='1' WHERE path like \"$deleteDir\%\;";
                        $result = mysql_query($query,$database);
			echo "ok";
		}else{
			echo "oops somethings wrong";
		}
}
function newFolder($name,$path){
	global $database,$dochome;

	$name = mysql_escape_string($name);
	$path = mysql_escape_string($path);

	$fullpath = getUserPath($path).$path.'/'.$name;

	if($path!=$dochome){
	$i = 1;
	$append = "";
	while(is_dir($fullpath.$append)){
		$append = " $i";
		$i++;
	}

	if(mkdir($fullpath.$append)){
		echo "{\"bindings\": [ { } ]}";
	}else{
		echo "{\"bindings\": [ {Errore nella creazione della Cartella} ]}";
	}

	}else{
		error('Impossibile creare una nuova Cartella in Faxweb');
	}
}
function checkLogin($how){
        jsonStart();
        if(isset($_SESSION['userid']) && $how=='OK'){
            jsonAdd("\"login\": \"true\",\"name\": \"$_SESSION[name]\"");
        }else if ($how=='KO' && $how=='0'){
            jsonAdd("\"login\": \"false\"");
        }else if ($how=='faxmanager'){
            jsonAdd("\"login\": \"faxmanager\"");
        }
        echo jsonReturn('userLogin');
}
function newPassword($current,$new){ /*
	$query = "select * from $GLOBALS[tablePrefix]users where id=$_SESSION[userid] and password=md5(\"G8,rMzw6BrBApLU9$current\")";
	$result = mysql_query($query);
	
	if(mysql_num_rows($result) == 1){
		logAction('newPassword',$_SESSION['user']);
		$pass = mysql_escape_string($_GET['pass']);
		$query = "update $GLOBALS[tablePrefix]users set `password`=md5(\"G8,rMzw6BrBApLU9$new\") where id=$_SESSION[userid]";
		$result = mysql_query($query)||die(mysql_error());
	}else{
		error("bad current password");
	} */
}
function userLogoff(){
	 session_destroy();
	 header('Location:index.php');
  	 exit;
}
function userLogin($username,$password){
	global $faxweb;
        $faxmanager_login=false;
	$_SESSION['userid'] = NULL;
	
        $login = new NethServiceAuth();
        $login->Authenticate($username, $password);

        $auth = $login->isLoggedIn();

        foreach($faxweb['faxmanager'] as $faxmanager_user)
                    {
                     if($username==$faxmanager_user)  $faxmanager_login= true;
                    }

        if($auth=='1' && $faxmanager_login==true) { 
                $_SESSION['userid']=$username;
                $_SESSION['user']=$username;
                $_SESSION['name']=$username;
                $_SESSION['password']=$password;
                $_SESSION['path']=array();
                $_SESSION['admin']=$username;
                userPermissions();

                logAction('login',$username);
                jsonAdd("\"login\": \"true\",\"name\": \"$_SESSION[name]\"");

        } else if ($auth=='1' && $faxmanager_login!=true){

               logAction('loginFailFaxManager',$username);
               jsonAdd("\"login\": \"faxmanager\"");

        } else {
               logAction('loginFail',$username);
               jsonAdd("\"login\": \"false\"");
        }
        echo jsonReturn('userLogin');
}
function userPermissions(){
	global $database;
	if(isset($_SESSION['userid'])){
                 $thispath ="/var/www/html/faxweb/faxweb";
                 $_SESSION['path']="/var/www/html/faxweb/faxweb";
                 $thispath = "faxweb";
                 $admin   = "1";
                 $_SESSION["auth.$thispath.view"]="1";
                 $_SESSION["auth.$thispath.rename"]="1";
                 $_SESSION["auth.$thispath.download"]="1";
                 $_SESSION["auth.$thispath.metaEdit"]="1";
                 $_SESSION["auth.$thispath.delete"]="1";
                 $_SESSION["auth.$thispath.move"]="1";
                 $_SESSION["auth.$thispath.folderRename"]="1";
                 $_SESSION["auth.$thispath.folderDelete"]="1";
                 $_SESSION["auth.$thispath.folderMove"]="1";
                 $_SESSION["auth.$thispath.newFolder"]="1";
                 $_SESSION["auth.$thispath.upload"]="1";
                 $cid = "1";
                 $_SESSION["auth.$cid.admin"]=1;
                 $_SESSION["admin.cid"][]="1";
	}
}
// internal functions //
function logAction($type,$details){
	global $database;
	$type = mysql_escape_string($type);
	$details = mysql_escape_string($details);
	$query = "insert into $GLOBALS[tablePrefix]log set user=\"$_SESSION[user]\",ip=\"$_SERVER[REMOTE_ADDR]\",type=\"$type\",details=\"$details\"";
	$result = mysql_query($query,$database);
}
function getUserAuth($type,$path){
	if(isset($_SESSION['userid'])){
		
		$paths = preg_split("/\//", $path); // isolate virtual directory name

		return (isset($_SESSION['auth.'.$paths[1].'.'.$type]))?$_SESSION['auth.'.$paths[1].'.'.$type]:false;
	}
}
function getFileInfo($fileid){
	global $database,$filepath,$fileinfo,$imageTypes;
	
	$fileid=mysql_escape_string($fileid);
	$query = "select * from $GLOBALS[tablePrefix]fax where id=$fileid";
	$result = mysql_query($query,$database);
	if(mysql_num_rows($result) == 0){
		error('bad fileid');
	}
	
	$file = mysql_fetch_assoc($result);
	
	$fileinfo['filename'] 		= $file['filename'];
	$fileinfo['date'] 		= $file['date'];
	$fileinfo['description'] 	= $file['description'];
	$fileinfo['downloads']		= $file['downloads'];
	$fileinfo['flags']		= $file['flags'];
	$fileinfo['type']		= $file['type'];
	$fileinfo['uploader']		= $file['uploader'];
	$fileinfo['path']		= $file['path'];
	$fileinfo['virtualpath']	= $file['rpath'];
	$fileinfo['size']		= filesize_format($file['size']);
	
	if(preg_match("$imageTypes",$fileinfo['type'])){
	      $fileinfo['image'] = 1;
	}else{
	      $fileinfo['image'] = 0;
	}
	$filePath = $fileinfo['path'];

	$filepath = $file['path'] . '/' . $file['filename'];
	$userpath = getUserPath($fileinfo['path']); // replaces / with \/ from preg_match

	if(preg_match("/$userpath/i",$filepath)){
		return true;
	}else{
		return false;
	}
}
function getUserPath($folderPath){
	global $database,$rootPath;
	if(isset($_SESSION['userid'])){
		$dirStructure = preg_split("/\//",$folderPath);
		$rootPath2 = (isset($dirStructure[1]))?$dirStructure[1]:'';
		$rootPath2 = mysql_escape_string($rootPath2);
		if($rootPath2==''){return '';}
                if($rootPath2=='faxweb') return mysql_escape_string($rootPath);
                else return '';
	} 
}
function databaseSync($folderpath,$realitivePath=''){
	global $database;
  	// get files from $folderpath and put them in array
	if (is_dir($folderpath)) {
		if ($dh = opendir($folderpath)) {
       					while (($file = readdir($dh)) !== false) {
					         if($file != '.' && $file != '..' && is_file($folderpath . '/' . $file) && substr($file,0,1) != '.'){
						           $fileid = fileid($folderpath,$file);
							   $files[$file] = array($fileid,'exist');
		 				 }
       					}
       					closedir($dh);
    		}
  	}
  	// get files from database
	$query = "select * from $GLOBALS[tablePrefix]fax where path=\"".mysql_escape_string($folderpath)."\"";
  	$result = mysql_query($query,$database);

  	while($dirinfo = mysql_fetch_assoc($result)) {
    							$filename = $dirinfo['filename'];
							$fileid =   $dirinfo['id'];

							if(isset($files[$filename])){
											$files[$filename][1]='done';
							} else {
								#databaseLost($fileid);
							}
  	}
	if(isset($files)){
			   $ak = array_keys($files);
			   for($i=0;$i<sizeof($ak);$i++){
	  						  $filename = $ak[$i];
	                                                  if($files[$filename][1]!='done'){
		                                                   if(databaseSearch($folderpath , $filename)){
		                                                                databaseUpdate($folderpath,$filename,$realitivePath);
		                                                   } else {
		                                                   }
	  						  }
			   }
        }
}
function databaseLost($fileid){
	global $database;
	$query = "update $GLOBALS[tablePrefix]fax set status=\"lost\" where id=$fileid";
	$result = mysql_query($query,$database) or die(mysql_error());
}
function databaseSearch($folderpath,$filename){
	global $database;

	$fileid = fileid($folderpath,$filename);
	$query = "select * from $GLOBALS[tablePrefix]fax where id=$fileid";
	$result = mysql_query($query,$database) or die(mysql_error());
	if($fileinfo = mysql_fetch_assoc($result)) {
    				if(file_exists($fileinfo['path'].'/'.$fileinfo['filename'])){

						  if($fileinfo['path'] == $folderpath && $fileinfo['filename'] == $filename){ return true;  
                                                                                                  // file was restored to origional location
						  } else {
 							    return false;       // exact file still exists somewhere else
	  					  }
				} else {
					  // file must have been moved
					  return true;

				}
  	} else {
    		// file is new
	  	return false;
  	}
}
function databaseUpdate($folderpath,$filename,$realitivePath){
	global $database,$finfo;
	$fileid = fileid($folderpath,$filename);
	$query = "update $GLOBALS[tablePrefix]fax set filename=\"$filename\",path=\"$folderpath\",rpath=\"$realitivePath\",status=\"found\" where id=$fileid";
	$result = mysql_query($query,$database);
}
function databaseAdd($folderpath,$filename,$realitivePath){
	global $database,$rootpath;

	if(function_exists('finfo')){
		$finfo = new finfo( FILEINFO_MIME,"$rootpath/inc/magic" );
		$type = $finfo->file( "$folderpath/$filename" );
	}else if(function_exists('mime_content_type') && mime_content_type("faxweb.php") != ""){
		$type = mime_content_type("$folderpath/$filename");
	}else{
		if(!$GLOBALS['mime']){
			include_once("inc/mimetypehandler.class.php");
			$GLOBALS['mime'] = new MimetypeHandler();
		}
		$type =  $GLOBALS['mime']->getMimetype("$filename");
	}

	$size = get_size($folderpath.'/'.$filename);
	
	$fileid = fileid($folderpath,$filename);
	
	while(!checkId($fileid)){
		$fileid++;
	}
	
	$query = "insert into $GLOBALS[tablePrefix]fax set id=\"$fileid\",filename=\"$filename\",path=\"$folderpath\",rpath=\"$realitivePath\",type=\"$type\",size=\"$size\"";
	$result = mysql_query($query,$database) or die(mysql_error());

	chmod($folderpath . '/' . $filename,0755);
	touch($folderpath . '/' . $filename,$fileid);
}
function checkId($id){
	$query = "select id from $GLOBALS[tablePrefix]fax where id=$id";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0){
		return true;
	}else{
		return false;
	}
}
function fileid($folderpath,$filename){
	$fileid = stat($folderpath . '/' . $filename);
	return $fileid[9];
}
function error($message){
	echo "{\"bindings\": [ {'error': \"$message\"} ]}";
	exit;
}
/*

THUMBNAIL

*/
function output_handler($in){
  	global $output;
	$output="$in";
}
function getThumb($fileid){
	global $database,$fileinfo;
	
	if(getFileInfo($fileid)){ // if a file type we want to deal with
		if(!checkThumb($fileid)){
			thumbnail($fileid);
		}
		
		$query = "select thumb from $GLOBALS[tablePrefix]fax where id=\"".mysql_escape_string($fileid)."\"";
		$result = mysql_query($query,$database);
		
		$fileThumb = mysql_fetch_assoc($result);
		header("Content-type:image/jpeg");
		echo $fileThumb['thumb'];
	}

}

function checkThumb($fileid){
	global $database;
	$query = "select id from $GLOBALS[tablePrefix]fax where id=\"".mysql_escape_string($fileid)."\" and thumb !=''";
	$result = mysql_query($query,$database);
	if(mysql_num_rows($result) == 0)
		return false;
	else
		return true;
}
function thumbnail($fileid){
	global $database,$fileinfo,$uploadDir,$imageTypes;
	$fileid=mysql_escape_string($fileid);
	if(getFileInfo($fileid) && preg_match("$imageTypes",$fileinfo['type']) ){
  		$deletefile = '';
        $file = $fileinfo['path'].'/'. $fileinfo['filename'];
        $tmpfname = tempnam("$uploadDir/tmp", "thumb").".png";
        exec("/usr/bin/convert -size 192x192 -depth 16 -alpha off -type Grayscale ".$file."[0] $tmpfname");
        $handle = fopen($tmpfname, "rb");
        $thumb = fread($handle, filesize($tmpfname));
        fclose($handle);
  		$query = "update $GLOBALS[tablePrefix]fax set thumb=\"".mysql_escape_string($thumb)."\" where id=\"$fileid\"";

  		$result = mysql_query($query,$database) || die("thumbnail: ".mysql_error());

	    unlink($tmpfname);
	}
}
/*
UPLOAD
*/
function upload($dir){

       	$userpath = getUserPath($dir).$dir;
    
        $tmp_name = $_FILES["upload"]["tmp_name"];
        $uploadfile = basename($_FILES['upload']['name']);
        $i=1;
            while(file_exists($userpath.'/'.$uploadfile)){
                $uploadfile = $i . '_' . basename($_FILES['upload']['name']);
                $i++;
            }
            
	move_uploaded_file($tmp_name, $userpath.'/'.$uploadfile);
	if(isset($_GET['redir'])){
		header("location: $_GET[redir]");
	}
}
function uploadAuth($path){
	global $uploadDir;
	$path = mysql_escape_string($path);
	jsonStart();
	
		$userpath = getUserPath($path).$path;
		if(is_dir($userpath)){
			$_SESSION['uploadPath'] = $path;
		if(file_exists($uploadDir."stats_".session_id().".txt"))
			unlink($uploadDir."stats_".session_id().".txt");
		if(file_exists($uploadDir."temp_".session_id()))
			unlink($uploadDir."temp_".session_id());
			jsonAdd("\"auth\":\"true\",\"sessionid\":\"".session_id()."\"");
		}else{
			jsonAdd("\"auth\":\"false\",\"error\":\"bad directory\"");
		}
		
	echo jsonReturn("bindings");
}
function uploadSmart(){
	global $uploadDir;

	if(!file_exists($uploadDir."stats_".session_id().".txt")){
		jsonStart();
		jsonAdd("\"percent\": 0, \"percentSec\": 0, \"speed\": \"0\", \"secondsLeft\": \"0\", \"done\": \"false\"");
		echo jsonReturn("bindings");
		exit();
	}

	$lines = file($uploadDir."stats_".session_id().".txt");
	jsonStart();

	$percent	=round(($lines[0]/100),3);
	$percentSec	=round($lines[1]/100,4);
	$speed		=filesize_format($lines[2]).'s';

	$secondsLeft	=secs_to_string(round($lines[3]));
	
	$size		=filesize_format($lines[4]).'s';

	if($percent == 1){
		// cleanup time
		if(isset($_SESSION['uploadPath'])){

			$path = $_SESSION['uploadPath'];
			$userpath = getUserPath($path).$path;

			$sessionid = session_id();

			$dh = opendir($uploadDir);
		    while (($file = readdir($dh)) !== false) {

		    	$sessionlen = strlen(session_id());
		    	if(substr($file,0,$sessionlen)==session_id()){
		    		$filename = substr($file,$sessionlen+1);
					$uploadfile=$filename;
					$i=1;
					while(file_exists($userpath.'/'.$uploadfile)){
					  $uploadfile = $i . '_' . $filename;
					  $i++;
			        }

					if(file_exists("$uploadDir$file") && !rename("$uploadDir$file","$userpath/$uploadfile")){
						echo "Error";
					}
				}
				
		    }closedir($dh);

		if(file_exists($uploadDir."stats_".session_id().".txt"))
		    	unlink($uploadDir."stats_".session_id().".txt");
		    if(file_exists($uploadDir."temp_".session_id()))
		    	unlink($uploadDir."temp_".session_id());

		}
		$done = "true";
	}else{
		$done = "false";
	}

	jsonAdd("\"percent\": $percent, \"size\": \"$size\",\"percentSec\": $percentSec, \"speed\": \"$speed\", \"secondsLeft\": \"$secondsLeft\", \"done\": \"$done\"");
	echo jsonReturn("bindings");
}
function deleteDir($dir)
{
	if (!$deletefile)
		return true;
	if (substr($dir, strlen($dir)-1, 1) != '/') $dir .= '/';
	if (is_dir($dir) && $handle = opendir($dir)){
				while ($obj = readdir($handle)){
							if ($obj != '.' && $obj != '..'){
										if (is_dir($dir.$obj)){
													if (!deleteDir($dir.$obj)) return false;
										} elseif (is_file($dir.$obj)){
													if (!unlink($dir.$obj)) return false;
										}
							}
				}
				closedir($handle);
				if (!@rmdir($dir)) return false;
				return true;
	}
	return false;
}
function get_size($path)
{
	if(!is_dir($path)) return filesize($path);
	return 0; #non gestiamo la dimensione dell directory
}
function filesize_format($size){

	if( is_null($size) || $size === FALSE || $size == 0 )
	return $size;

	if( $size > 1024*1024*1024 ) $size = sprintf( "%.1f GB", $size / (1024*1024*1024) );
	elseif( $size > 1024*1024 ) $size = sprintf( "%.1f MB", $size / (1024*1024) );
	elseif( $size > 1024 ) $size = sprintf( "%.1f kB", $size / 1024 );
	elseif( $size < 0 ) $size = '&nbsp;';
	else $size = sprintf( "%d B", $size );

	return $size;
}
function secs_to_string ($secs, $long=false) {
	$initsecs = $secs;
	// reset hours, mins, and secs we'll be using
	$hours = 0;
	$mins = 0;
	$secs = intval ($secs);
	$t = array(); // hold all 3 time periods to return as string
  
	// take care of mins and left-over secs
	if ($secs >= 60) {
				$mins += (int) floor ($secs / 60);
				$secs = (int) $secs % 60;
        
    				// now handle hours and left-over mins    
    				if ($mins >= 60) {
						      $hours += (int) floor ($mins / 60);
						      $mins = $mins % 60;
    						 }
				// we're done! now save time periods into our array
				$t['hours'] = (intval($hours) < 10) ? "" . $hours : $hours;
				$t['mins'] = (intval($mins) < 10) ? "" . $mins : $mins;
	}

	// what's the final amount of secs?
	$t['secs'] = (intval ($secs) < 10) ? "" . $secs : $secs;
  
	// decide how we should name hours, mins, sec
	$str_hours = ($long) ? "hour" : "hour";
	$str_mins = ($long) ? "minute" : "min";
	$str_secs = ($long) ? "second" : "sec";

	// build the pretty time string in an ugly way
	$time_string = "";
  
  	$time_string .= ($t['hours'] > 0) ? $t['hours'] . " $str_hours" . ((intval($t['hours']) == 1) ? " " : "s ") : "";
	$time_string .= ($t['mins']) ? $t['mins'] . " $str_mins" . ((intval($t['mins']) == 1) ? " " : "s ") : "";
  
	if($initsecs < 120){
				$time_string .= ($t['secs']) ? $t['secs'] . " $str_secs" . ((intval($t['secs']) == 1) ? "" : "s ") : " ";
  	} else {
    		if($secs > 30){
				$pre = ">";
		} else {
			$pre = "about";
		}
  	$time_string = "$pre $time_string";
  	}
	return empty($time_string) ? 0 : $time_string;
}
function jsonStart(){
	global $json;
	$json = '';
}
function jsonAdd($jsonLine){
	global $json;
	if($json != '')
	$json .= ",";
	$json .= "{ $jsonLine }";
}
function jsonReturn($variableName){
	global $json;
	return "{\"bindings\": [ $json ]}";
}
?>
