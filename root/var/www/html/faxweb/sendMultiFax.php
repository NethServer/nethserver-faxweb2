<?
include_once("faxweb.php");

$login = new NethServiceAuth();

$login->Authenticate($_SESSION['name'],$_SESSION['password']);

$auth = $login->isLoggedIn();

if($auth=='1') {
?>
<html>
<body>
<?

global $uploadDir,$dochome,$database,$dbrubrica,$notify;

$today_ts = time();
$today = date("y_m_d_G-i-s",$today_ts);
$today2 = date("Y-m-d G:i:s",$today_ts);
$id_m   = date("Ymd-G-i",$today_ts);
$filename=NULL;


if($_POST["description"]==NULL ) {

     #Se l'operazione e' fallita...
     $message = '1'; ?>
     <script type="text/javascript">
     <!--

     parent.Error_desc();

     -->
     </script>
<?
}
$_POST["description"]=mysql_escape_string($_POST["description"]);
$uploaddir = $uploadDir.'/elenco_inviati/';
$rec_file_tmp = $_FILES['rec_file']['tmp_name'];
$up_rec_filename->Value = html_entity_decode($_FILES['rec_file']['name']);
if (!move_uploaded_file($rec_file_tmp, $uploaddir . $up_rec_filename->Value)) {
      $up_rec_filename->Value = NULL;
}

$uploaddir = $uploadDir.'/tmp/';

$userfile_tmp = $_FILES['multifax_file']['tmp_name'];
$up_pdf_filename->Value =  html_entity_decode($_FILES['multifax_file']['name']);
$filetosend = $uploaddir.$up_pdf_filename->Value;

if (move_uploaded_file($userfile_tmp,$filetosend))
 {
  $filetype=`file -b "$uploaddir$up_pdf_filename->Value"`;
  if (!preg_match("/^(PDF|TIFF|PostScript)/", $filetype)) { 
     $message = '1'; ?>
     <script type="text/javascript">
     <!--

     parent.Error_fax_file();

     -->
     </script>
<?
  }
} else {
     #Se l'operazione e' fallita...
     $message = '1'; ?>
     <script type="text/javascript">
     <!--

     parent.Error_upload();

     -->
     </script>
<?
}

if($message==NULL){
     #se non ci sono problemi per caricare il file (o se devo solo reinviarlo)
     $nome_lista = "el_$today";

     #inizializzo l'array con i destinatari
     $dest_array = array();

     #se e' stata caricata estraggo i numeri e i nomi dalla lista di
     #destinatari
if ($up_rec_filename->Value!=NULL) {
        $caratteri = array("\\", "/", "-", "#", " ","\"");
        $lista_txt = fopen($uploadDir."/elenco_inviati/".$up_rec_filename->Value."", "r");
        while (!feof($lista_txt)) {
           $riga=rtrim(fgets($lista_txt,256));
           if($riga!=NULL) {
              $arr_row = preg_split("/,/",$riga);
              $dest_array[str_replace($caratteri, "", $arr_row[0])]=mysql_escape_string($arr_row[1]);
           }
        }
       fclose($lista_txt);

} elseif($GLOBALS['phonebook']=="horde" && $up_rec_filename->Value==NULL) {

   foreach($_POST["selezione"] as $object_id)
      {
       if(substr($object_id,0,2)=='D-' && is_numeric(substr($object_id,2))) {

         $dest_array[substr($object_id,2)]=NULL;

      } else {
       $query = "SELECT * FROM turba_objects_pub where object_id='".$object_id."';";
       $result = mysql_query($query,$dbrubrica );
       $object_row=mysql_fetch_object($result);

       if($object_row->object_type=='Group') {
          $grp_tot = preg_split("/\:/", $object_row->object_members);
          $list = preg_split("/\"/", $object_row->object_members);
          for($gi=0;$gi<=($grp_tot[1]-1);$gi++) {
               #ricavo la destinazione corrispondente all'object_id
               $query2 = "SELECT * FROM turba_objects_pub where object_id='".$list[$gi*2+1]."';";
               $result2 = mysql_query($query2, $dbrubrica);
               $dest=mysql_fetch_object($result2);
               #la inserisco nell'array
               $dest_array[$dest->object_fax] = $dest->object_name;
            }
      }
        else {
             if ($object_row->object_fax<>'') {
                 $dest_array[$object_row->object_fax] = $object_row->object_name;
             }
        }
      }
     }
} elseif ($GLOBALS['phonebook']=="vtiger" && $up_rec_filename->Value==NULL) { 


   foreach($_POST["selezione"] as $object_id)
    {

      if(substr($object_id,0,2)=='C-') {

         $query="SELECT * from vtiger_contactdetails WHERE contactid='".substr($object_id,2)."';";
         $result = mysql_query($query,$dbrubrica );
         $object_row=mysql_fetch_object($result);
         $name=$object_row->lastname." ".$object_row->firstname;

         if ($object_row->fax<>'') {
                 $dest_array[$object_row->fax] = $name;

         }
      } elseif(substr($object_id,0,2)=='D-' && is_numeric(substr($object_id,2))) {

         # NUMERO DIRETTO
         $dest_array[substr($object_id,2)]=NULL;

      } else {
         $query="SELECT * from vtiger_account WHERE accountid='".$object_id."';";
         $result = mysql_query($query,$dbrubrica );
         $object_row=mysql_fetch_object($result);

         if ($object_row->fax<>'') {
                 $dest_array[$object_row->fax] = $object_row->accountname;

         }
      }
    }
} else if($GLOBALS['phonebook']=="custom"  && $up_rec_filename->Value==NULL) {

   foreach($_POST["selezione"] as $object_id)
      {
       $query = "SELECT * FROM phonebook where fax='".$object_id."';";
       $result = mysql_query($query,$dbrubrica );
       $object_row=mysql_fetch_object($result);

       if ($object_row->fax<>'') {
                 $dest_array[$object_row->fax] = $object_row->nome;
             }
      }
} else { 

  foreach($_POST["selezione"] as $object_id)
    {
     $dest_array[substr($object_id,2)]=NULL;
    }
}
    
  $f=fopen($uploadDir.'/elenco_inviati/temp','w');
  $fd_temp_name=fopen($uploadDir.'/elenco_inviati/temp_name','w');

  for ($n = 0; $n <count($dest_array); $n++) {
     $line = each ($dest_array);
     fwrite($f,$line[key]."\n");
     fwrite($fd_temp_name,$line[key].",".$line[value]."\n");
  }
  fclose($f);
  fclose($fd_temp_name);

  if($_POST["multis_day"]=="Day" && $_POST["multis_time"]!=NULL) $send_date= $_POST["multis_time"] * 24;
  else if ($_POST["multis_time"]!=NULL)  $send_date = $_POST["multis_time"];

  if ($send_date!= NULL) $OPT2=" -a 'now + $send_date hour' ";

  if($_POST["multik_day"]=="Day" && $_POST["multik_time"]!=NULL) $kill_date= $_POST["multik_time"] * 24;
  else if ($_POST["multik_time"]!=NULL) $kill_date= $_POST["multik_time"];
  else $kill_date= '3';

  if ($send_date!= NULL) $kill_date= 'now + '.($kill_date + $send_date).' hour';
  else $kill_date= 'now + '.$kill_date.' hour';

  #conto i numeri di telefono per impostare la priorità
  $lines=file($uploadDir.'/elenco_inviati/temp');
  $destinatari=0;
  foreach($lines as $line_num => $linea) {
    $destinatari++;
  }

 
  mkdir("$dochome/sentm/$id_m");
  exec("chmod go+r $filetosend");

  if($destinatari > 5) $OPT1=" -P bulk ";

  if($notify)
	$R = " -R ";
  else
	$R = " ";
  $id_m="faxweb%$id_m";
  system('sendfax '.$_POST["multiquality"].$OPT2.' '.$R.' -f '.$_SESSION[name].' -o '.$_SESSION[name].' -k "'.$kill_date.'" -i '.$id_m.' -n '.$OPT1.' -z '.$uploadDir.'/elenco_inviati/temp \''.$filetosend.'\'> '.$uploadDir.'/elenco_inviati/listajob_'.$today.'');

  logAction('sendMultiFax',$_POST["description"]);

  #elimino file temporanei
    @unlink($filetosend);
    @unlink($uploadDir.'/elenco_inviati/temp');
    @unlink($uploadDir.'/elenco_inviati/temp_name');
    @unlink($uploadDir.'/elenco_inviati/listajob_'.$today);
    @unlink($uploadDir.'/elenco_inviati/'.$nome_lista);
?>                      
<script type="text/javascript">
  <!--

parent.Send_Fax4();

  -->
</script>

<?  }
 else {
exec('rm -rf '.$dochome.'/sentm/'.$id_m);

 }
 $up_rec_filename->Value=NULL;
 $up_pdf_filename->Value=NULL;

###########################################################################################
?>
</body>
</html>
<?

} else {

      header("Location: index.php");
}
?>
