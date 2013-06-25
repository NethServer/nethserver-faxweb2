<?php

include_once("conf.inc.php");
include_once("login.php");

$login = new NethServiceAuth();

$login->Authenticate($_SESSION['name'],$_SESSION['password']);

$auth = $login->isLoggedIn();

if($auth=='1') {

$path= $_GET["id"];

global $database;

$query = "SELECT id_m FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and deleted!='1' and status='found' limit 1;";
$result = mysql_query($query,$database);
$id_m = mysql_result($result,0);

$query2 = "SELECT descrizione FROM ".$GLOBALS['tablePrefix']."documents where id_m='".$id_m."';";
$result2 = mysql_query($query2,$database);
$descrizione = mysql_result($result2,0);

$filename = $descrizione.".xls";
$filename = str_replace(" ","_", $filename);
header ("Content-Type: application/vnd.ms-excel");
header ("Content-Disposition: inline; filename=$filename");

$query3 = "SELECT * FROM ".$GLOBALS['tablePrefix']."fax where path='".$path."' and status='found' and deleted!='1';";
$result3 = mysql_query($query3,$database) ;
$list = array();
while($files = mysql_fetch_array($result3, MYSQL_ASSOC)) array_push($list, $files);
?>

<html>
<body>
<table>
 <tr>
  <td>Destinatario</td>
  <td>Numero Destinatario</td>
  <td>Data Invio</td>
  <td>Descrizione</td>
  <td>Pagine</td>
  <td>Inviato da</td>
  <td>Stato</td>
  <td>Esito</td>
  <td>Tentativi</td>
  <td>Reinvii</td>
  <td>Dettagli Reinvii</td>
  <td>Job Id</td>
 </tr> 
<?php
    $S7=0;  // INVIATI
    $S8=0;  // ERRORI
    $SOTHER=0;  // RITRASMESSI
    $SNULL=0;  // IN ATTESA

      foreach ($list as $files){

                                 if ($files[state]==7) $S7++;
                                 elseif ($files[state]==8) $S8++;
                                 elseif ($files[state]==NULL) $SNULL++;
                                 else $SOTHER++;

                                 ?>
                                 <tr>
                                  <td><?php echo $files["name"]; ?></td>
                                  <td><?php echo $files["number"]; ?></td>
                                  <td><?php echo $files["date"]; ?></td>
                                  <td><?php echo $files["description"]; ?></td>
                                  <td><?php echo $files["pages"]; ?></td>
                                  <td><?php echo $files["user"]; ?></td>
                                  <td><?php if ($files["state"] == '') $state = "Invio in Corso";
                                         if ($files["state"] =='1') $state = "Sospeso";
                                         if ($files["state"] =='2') $state = "In Attesa di invio all'orario stabilito";
                                         if ($files["state"] =='3') $state = "Problemi di connessione,in attesa di ritrasmissione";
                                         if ($files["state"] =='4') $state = "Numero Occupato";
                                         if ($files["state"] =='5') $state = "Pronto ad essere inviato";
                                         if ($files["state"] =='6') $state = "Invio in corso...";
                                         if ($files["state"] =='7') $state = "Inviato Correttamente";
                                         if ($files["state"] =='8') $state = "Non Inviato";
                                         if ($files["state"]=='99') $state = "Interrotto dall'utente"; 
                                         echo $state; ?></td>
                                  <td><? if ($files["state"] =='7') $esito = "OK";
                                         else $esito = $files["esito"];
                                         echo $esito; ?></td>
                                  <td><? echo $files["attempts"]; ?></td>
                                  <td><? echo $files["resends"]; ?></td>
                                  <td><? echo $files["resend_rcp"]; ?></td>
                                  <td><? echo $files["job_id"]; ?></td>
                                 </tr>
                          <?  } ?>
<tr>
 <td colspan='3'>Inviati: <? echo $S7; ?></td> 
 <td colspan='3'>Errori: <? echo $S8; ?></td> 
 <td colspan='3'>Ritrasmessi: <? echo $SOTHER; ?></td> 
 <td colspan='3'>In Attesa: <? echo $SNULL; ?></td> 
</tr>
</table>
</body>
</html>
<?
} else {

      header("Location: index.php");
}
?>
