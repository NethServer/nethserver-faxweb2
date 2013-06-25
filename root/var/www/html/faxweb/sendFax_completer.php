<?

include_once("conf.inc.php");
include_once("login.php");

$login = new NethServiceAuth();

$login->Authenticate($_SESSION['name'],$_SESSION['password']);

$auth = $login->isLoggedIn();

if($auth=='1') {

global $dbrubrica;

if($_REQUEST['autocomplete_parameter']=='*') { $_REQUEST['autocomplete_parameter']=''; }

if($GLOBALS['phonebook']=="horde") {

$query = "SELECT * FROM turba_objects_pub WHERE object_name LIKE '".$_REQUEST['autocomplete_parameter']."%' OR object_company LIKE '".$_REQUEST['autocomplete_parameter']."%' OR object_fax LIKE '".$_REQUEST['autocomplete_parameter']."%' order by object_name limit 100;";
$result = mysql_query($query,$dbrubrica);

if($_REQUEST['name']=='SendList_select') echo '<select name="InviaElenco[]" id="InviaElenco" size="12" class="invia" onChange="Select3();">';
else if ($_REQUEST['name']=='SendList3_select') echo '<select name="MultiInviaElenco" id="MultiInviaElenco" size="12" class="invia2" onDblClick="Select();">';

while($contact = mysql_fetch_assoc($result)) {

$contact["object_name"]= str_replace("\"", "\\\"",$contact["object_name"]);
$contact["object_company"]= str_replace("\"", "\\\"",$contact["object_company"]);
$name_tmp=str_pad(substr($contact["object_name"],0,28),28);
$company_tmp=str_pad(substr($contact["object_company"],0,28),28);
$name= str_replace(" ", "&nbsp;",$name_tmp);
$company= str_replace(" ", "&nbsp;",$company_tmp);

echo "<option value=\"".$contact["object_id"]."\">".$name."&nbsp;&nbsp;".$company."&nbsp;&nbsp;".$contact["object_fax"]."</option>";
}
echo '</select>';

} elseif ($GLOBALS['phonebook']=="vtiger") {

$query2 = "SELECT accountid as id, fax as number, accountname as name, '' as azienda FROM vtiger_account WHERE ( accountname LIKE '".$_REQUEST['autocomplete_parameter']."%' OR fax LIKE '".$_REQUEST['autocomplete_parameter']."%') AND  fax!='' UNION ALL SELECT concat('C-',contactid) as id, vtiger_contactdetails.fax as number,concat(lastname,' ',firstname) as name, accountname as azienda FROM vtiger_contactdetails inner join vtiger_account on vtiger_contactdetails.accountid=vtiger_account.accountid WHERE (vtiger_contactdetails.fax LIKE '".$_REQUEST['autocomplete_parameter']."%' OR lastname LIKE '".$_REQUEST['autocomplete_parameter']."%' OR accountname  LIKE '".$_REQUEST['autocomplete_parameter']."%') AND vtiger_contactdetails.fax!='' order by name limit 100;";
$result2 = mysql_query($query2,$dbrubrica);

if($_REQUEST['name']=='SendList_select') echo '<select name="InviaElenco[]" id="InviaElenco" size="12" class="invia" onChange="Select3();">';
else if ($_REQUEST['name']=='SendList3_select') echo '<select name="MultiInviaElenco" id="MultiInviaElenco" size="12" class="invia2" onDblClick="Select();">';

while($contact = mysql_fetch_assoc($result2)) {

$contact["name"]= str_replace("\"", "\\\"",$contact["name"]);
$contact["azienda"]= str_replace("\"", "\\\"",$contact["azienda"]);
$name_tmp=str_pad(substr($contact["name"],0,28),28);
$company_tmp=str_pad(substr($contact["azienda"],0,28),28);
$name= str_replace(" ", "&nbsp;",$name_tmp);
$company= str_replace(" ", "&nbsp;",$company_tmp);

echo "<option value=\"".$contact["id"]."\">".$name."&nbsp;&nbsp;".$company."&nbsp;&nbsp;".$contact["number"]."</option>";
}
echo '</select>';

} elseif ($GLOBALS['phonebook']=="custom") {

$query3 = "SELECT * FROM phonebook  WHERE name LIKE '".$_REQUEST['autocomplete_parameter']."%' OR fax LIKE '".$_REQUEST['autocomplete_parameter']."%' OR company LIKE '".$_REQUEST['autocomplete_parameter']."%' order by name limit 100;";
$result = mysql_query($query3,$dbrubrica);

if($_REQUEST['name']=='SendList_select') echo '<select name="InviaElenco[]" id="InviaElenco" size="12" class="invia" onChange="Select3();">';
else if ($_REQUEST['name']=='SendList3_select') echo '<select name="MultiInviaElenco" id="MultiInviaElenco" size="12" class="invia2" onDblClick="Select();">';

while($contact = mysql_fetch_assoc($result)) {

$contact["name"]= str_replace("\"", "\\\"",$contact["name"]);
$contact["company"]= str_replace("\"", "\\\"",$contact["company"]);
$name_tmp=str_pad(substr($contact["name"],0,28),28);
$company_tmp=str_pad(substr($contact["company"],0,28),28);
$name= str_replace(" ", "&nbsp;",$name_tmp);
$company= str_replace(" ", "&nbsp;",$company_tmp);

echo "<option value=\"".$contact["fax"]."\">".$name."&nbsp;&nbsp;".$company."&nbsp;&nbsp;".$contact["fax"]."</option>";
}
echo '</select>';
}
} else {

      header("Location: index.php");
}

?>
