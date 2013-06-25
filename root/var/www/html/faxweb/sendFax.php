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

$up_rec_filename->Value = NULL;

$uploaddir = $uploadDir.'/tmp/';

$userfile_tmp = $_FILES['fax_file']['tmp_name'];
$up_pdf_filename->Value =  html_entity_decode($_FILES['fax_file']['name']);
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
     #Se l'operazione è fallita...
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
     $object_id=$_POST["InviaElenco"][0];

if($GLOBALS['phonebook']=="horde") {

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
} elseif ($GLOBALS['phonebook']=="vtiger") { 

      if(substr($object_id,0,2)=='C-') {
        
         $query="SELECT * from vtiger_contactdetails WHERE contactid='".substr($object_id,2)."';";
         $result = mysql_query($query,$dbrubrica );
         $object_row=mysql_fetch_object($result);
         $name=$object_row->lastname." ".$object_row->firstname;

         if ($object_row->fax<>'') {
                 $dest_array[$object_row->fax] = $name;

         }
      } else {
         $query="SELECT * from vtiger_account WHERE accountid='".$object_id."';";
         $result = mysql_query($query,$dbrubrica );
         $object_row=mysql_fetch_object($result);

         if ($object_row->fax<>'') {
                 $dest_array[$object_row->fax] = $object_row->accountname;

         }
      }
} elseif ($GLOBALS['phonebook']=="custom") {

         $query="SELECT * from phonebook  WHERE fax='".$object_id."';";
         $result = mysql_query($query,$dbrubrica);
         $object_row=mysql_fetch_object($result);

         if ($object_row->fax<>'') {
                 $dest_array[$object_row->fax] = $object_row->nome;

         }
}



  # NUMERO DIRETTO
  if($_POST["cerca"]!=NULL && is_numeric($_POST["cerca"]) && $_POST["InviaElenco"][0]==NULL) {
     $dest_array[$_POST["cerca"]]=NULL;
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


  if($_POST["s_day"]=="Day" && $_POST["s_time"]!=NULL) $send_date= $_POST["s_time"] * 24;
  else if ($_POST["s_time"]!=NULL)  $send_date = $_POST["s_time"];

  if ($send_date!= NULL) $OPT2=" -a 'now + $send_date hour' ";

  if($_POST["k_day"]=="Day" && $_POST["k_time"]!=NULL) $kill_date= $_POST["k_time"] * 24;
  else if ($_POST["k_time"]!=NULL) $kill_date= $_POST["k_time"];
  else $kill_date= '3';

  if ($send_date!= NULL) $kill_date= 'now + '.($kill_date + $send_date).' hour';
  else $kill_date= 'now + '.$kill_date.' hour';

  #conto i numeri di telefono per impostare la priorita'
  $lines=file($uploadDir.'/elenco_inviati/temp');
  $destinatari=0;
  foreach($lines as $line_num => $linea) {
    $destinatari++;
  }


  $OPT1=" -P ".$_POST["priority"];

  if($notify)
	$R = " -R ";
  else
	$R = " ";

  system('sendfax '.$_POST["quality"].$OPT2.' '.$R.' -f '.$_SESSION[name].' -o '.$_SESSION[name].' -k "'.$kill_date.'" -n '.$OPT1.' -z '.$uploadDir.'/elenco_inviati/temp \''.$filetosend.'\'> '.$uploadDir.'/elenco_inviati/listajob_'.$today.'');


    #elimino tutti i file temporanei
    @unlink($filetosend);
    @unlink($uploadDir.'/elenco_inviati/temp');
    @unlink($uploadDir.'/elenco_inviati/temp_name');
    @unlink($uploadDir.'/elenco_inviati/listajob_'.$today);
    @unlink($uploadDir.'/elenco_inviati/'.$nome_lista);
?>                      
<script type="text/javascript">
  <!--

parent.Send_Fax2();

  -->
</script>

<?  }
 else {

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
