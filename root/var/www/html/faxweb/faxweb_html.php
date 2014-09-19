<?php
include_once("login.php");

$login = new NethServiceAuth();

$login->Authenticate($_SESSION['name'],$_SESSION['password']);

$auth = $login->isLoggedIn();

if($auth=='1') {
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="shortcut icon" href="images/favicon.ico" >
<link rel="stylesheet" type="text/css" media="all" href="css/calendar-win2k-cold-1.css">
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>FaxWeb 2.0</title>
<script src="js/prototype.js" type="text/javascript"> </script>
<script src="js/builder.js" type="text/javascript"> </script>
<script src="js/effects.js" type="text/javascript"> </script>
<script src="js/dragdrop.js" type="text/javascript"> </script>
<script src="js/controls.js" type="text/javascript"></script>
<script src="js/faxweb.js" type="text/javascript"></script>
<script src="js/search.js" type="text/javascript"></script>
<script src="js/calendar.js" type="text/javascript"></script>
<script src="js/calendar-setup.js" type="text/javascript"></script>
<script src="js/lang/calendar-it.js" type="text/javascript"></script>
<script type="text/javascript">
	folderIcon = 'images/mac_dir.png';
	fileIcon = 'images/mac_file.png';
	collapsed = 'images/collapsed.png';
	expanded = 'images/collapsed.png';
	spinnerIcon = 'images/spinner_blue.gif';
	deleteIcon = 'images/delete.png';
	saveIcon = 'images/savebtn.jpg';
	addIcon = '';
	removeIcon = 'images/bullet_delete.png';
	renameIcon = '';
	vcollapsed = 'images/virtual-collapsed.png';
	vexpanded = 'images/virtual-expanded.png';
	uploadCancel = 'images/cancelupload.jpg';
	uploadBtn = 'images/upload.jpg';
</script>

<script type="text/javascript">
<!--
var SearchCompleter = Class.create();
SearchCompleter.prototype = {

initialize: function (input_id, select_id, url) {
        this.input     = $(input_id);
        this.select    = $(select_id);
        this.url       = url;
        this.ajwaiting = false;
        Event.observe(this.input, "keyup", this.getChoices.bindAsEventListener(this));
},

getChoices: function () {
        if (this.ajwaiting == true) return;
        if (this.input.value!='') {
        this.ajwaiting = true;
        var params = $H({ autocomplete_parameter: this.input.value , name: this.select.id });
        var ajax = new Ajax.Request(this.url , {
                onSuccess: this.getChoices_handler.bind(this),
                method: 'post',
                parameters: params.toQueryString(),
                onFailure: function() { showError(ER.ajax); }
        }); }
},

getChoices_handler: function (response) {

        this.select.innerHTML = response.responseText;
        this.ajwaiting = false;
}
};
-->
</script>

<style type="text/css">
	@import url(css/faxweb.css);
</style>
<style type="text/css">
#dashboard {
	/* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
  position: absolute; left: 25px; top: 124px;
}
body > div#dashboard {
  /* used by Opera 5+, Netscape6+/Mozilla, Konqueror, Safari, OmniWeb 4.5+, iCab, ICEbrowser */
  position: fixed;
}
<style type="text/css">
#dashboard {
	/* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
  position: absolute; left: 25px; top: 124px;
}
body > div#dashboard {
  /* used by Opera 5+, Netscape6+/Mozilla, Konqueror, Safari, OmniWeb 4.5+, iCab, ICEbrowser */
  position: fixed;
}
</style>
<!--[if gte IE 5.5]>
<![if lt IE 7]>
<style type="text/css">
div#dashboard {
  /* IE5.5+/Win - this is more specific than the IE 5.0 version */
  left: expression( ( 25 + ( ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ) ) + 'px' ) ;
  top: expression( ( 124 + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ) ) + 'px' ) ;
}
</style>
<![endif]>
<![endif]-->
</head>
<body onresize="Resize()">
<table class="faxweb">
 <tr>
  <td class="banner">
   <table>
    <tr>
     <td><img src="images/faxweb.gif" title="FaxWeb 2.0 @ 2007 Nethesis srl" alt="FaxWeb 2.0 @ 2007 Nethesis s.r.l." onclick="Nethesis();"/>
          <div id="head_words">FaxWeb</div>
          <div id="head_words2">2.0</div></td>
     <td class="pulsante"><a href="#" onclick="OpenPopup();" title="Invio Singolo Fax" class="pulsante1"><img src="images/send_fax.png"><br>Invio<br>Singolo</a></td>
     <td class="pulsante"><a href="#" onclick="OpenPopup2();" title="Invio Multiplo Fax" class="pulsante2"><img src="images/send_multifax.png"><br>Invio<br>Multiplo</a></td>
     <td class="pulsante"><a href="#" onclick="FaxStat();" title="Stato Fax" class="pulsante3"><img src="images/info.png"><br>Stato<br>Fax</a></td>
     <td class="pulsante"><a href="#" onclick="newFolder(); return false" title="Crea una Cartella" class="pulsante4"><img src="images/folder_open.png"><br>Nuova<br>Cartella</a></td>
     <td class="pulsante"><a href="#" onclick="deleteFolder(); return false" title="Crea una Cartella" class="pulsante5"><img src="images/folder_create.png"><br>Elimina<br>Cartella</a></td>
     <td class="pulsante"><a href="#" onclick="updateAll(root); return false" title="Aggiorna" class="pulsante6"><img src="images/cache.png"><br>Aggiorna</a></td>
     <td>
      <div id="search_form">
       <form onsubmit="search.start(); return false">
           <input type="text" name="searchbar" id="searchbar" value="" />
           <input type="image" align="center" src="images/cerca.png" title="Ricerca Veloce" alt="Ricerca Veloce" name="searchbtn" id="searchbtn" alt="[Submit]" />
       </form>
      </div>
     </td>
     <td class="pulsante"><a href="#" onclick="OpenPopup4();" title="Ricerca Avanzata" class="pulsante7"><img src="images/ricerca.png"><br>Ricerca<br>Avanzata</a></td>
     <td class="pulsante"><a href="faxweb.php?faxweb=userLogoff" title="Esci" class="pulsante8"><img src="images/exit.png"><br>Esci</a></td> 
     <td> <div id="pagine"><table class="pagine"><tr><td>
      <a  href="#" onclick="PageStart(); return false" title="Inizio" class="pages"><img src="images/start.png"></a>
      <a  href="#" onclick="PageDown(); return false" title="Indietro" class="pages"><img src="images/left.png"></a><span id="pageNumber" name="pageNumber" class="pageNumber"></span>
      <a  href="#" onclick="PageUp(); return false" title="Avanti" class="pages"><img src="images/right.png"></a>
      <a  href="#" onclick="PageEnd(); return false" title="Fine" class="pages"><img src="images/end.png"></a></td></tr></table>
      </div>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td class="td_DirTitle"><div id="DirTitle"><table class="headform3"><tr><td class="titolo_dir">Cartelle</td></tr></table></div></td>
  <td class="td_FileTitle"><div id="FileTitle"></div></td>
 </tr>
 <tr>
  <td class="left_part">
   <div id="Left_content">
         <div id="dirList"></div>
         <div id="informationcart2">
           <table class="headform5"><tr><td class="titolo_file">&nbsp;Dettagli<img src="images/apri.png" class="chiudi4" onclick="OpenPopup3();"></td></tr></table>
           <div id="informationcart"><div id="meta"><p>Nessun oggetto selezionato</p></div></div>
         </div>
   </div>
  </td>
  <td class="right_part">
    <div id="fileList"></div>
  </td>
 </tr> 
</table>

<div id="searcharea"><div id="searchfiletitle"></div></div>

<div id="preview"></div>
<div id="inoltri"></div>
<div id="rec_dett"></div>
<div id="send_dett"></div>
<div id="resend"></div>

<div id="faxStat">
 <table class="headform">
  <tr><td class="button3">&nbsp;<span class="title">Stato Fax</span><img src="images/aggiorna.png" class="aggiorna" title="Aggiorna" onclick="FaxStatUpdate();"><img src="images/Close.gif" class="chiudi3" title="Chiudi" onclick="ClosePopup6();"></td></tr>
  <tr><td id="modemStat" name="modemStat" class="modemStat"></td></tr>
 </table>
</div>

<div id="InoltraList">
 <table class="headform">
 <form name="forward">
  <tr> <td class="button3">&nbsp;<span class="title">Inoltra a ... </span><img src="images/Close.gif" title="Chiudi" class="chiudi5" onclick="ClosePopup();">
  <br> &nbsp;<span class="mostra" style='display:none'>Mostra:</span>&nbsp;&nbsp;&nbsp; 
     <select name="CambiaMostra" id="CambiaMostra" size="1" class="mostra" onChange="Change(this);" style='display:none'>
        <option value="all">Tutto</option>
        <option value="user">Utenti</option>
        <option value="group">Gruppi</option>
        </select>
    </td>
  </tr>
  <tr style='display:none'> <td><select type='hidden' name="InoltraElenco" id="InoltraElenco" size="12" multiple="multiple" class="mail" onChange="Numbers();"></select></td></tr>
  <tr> <td>Mail:&nbsp;&nbsp;<input class="testo" type="text" name="address" maxlength="70"></td></tr>
  <tr> <td>Note d'inoltro:&nbsp;&nbsp;</td></tr>
  <tr> <td><textarea name="notes" id="notes" rows="3" cols="35"></textarea></td></tr>
  <tr> <td class="button3" >&nbsp;<span class="title3"  style='display:none'>Selezionati:</span><span  style='display:none' id="numero" name="numero" class="numero">&nbsp;&nbsp;</span><img src="images/email2.png" class="invia" title="Invia" onclick="send_mail(this);"></td></tr>
 </form>
 </table>
</div>

<div id="SendList">
 <form name="send_fax" enctype="multipart/form-data" method="post" target="uploadiframe" action="sendFax.php" onsubmit="return true">
 <table class="headform6">
  <tr> <td class="button1" colspan="2">&nbsp;<span class="title">Invia Fax: Cerca il Destinatario</span><img src="images/Close.gif" class="chiudi" title="Chiudi" onclick="ClosePopup2();"></td></tr>
  <tr> <td>Cerca o Invia Direttamente:&nbsp;<input class="testo" type="text" name="cerca" id="cerca" maxlength="20" ></td></tr>
<? if ($GLOBALS['phonebook'] == 'none') { ?>
  <tr> <td height="155px"><table><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td><font color="red" size="3pt">Nessuna Rubrica Selezionata</font></td></tr></table></td></tr>
<? } else { ?>
  <tr> <td id="SendList_select"><select name="InviaElenco[]" id="InviaElenco" size="12" class="invia" onChange="Select3();">
       <option value="0">&nbsp;</option></select></td></tr> 
<? } ?>
  <tr> <td>Sono supportati i formati pdf, tiff e postscript (ps).</td></tr>
  <tr> <td>Seleziona il file da inviare:&nbsp;&nbsp;<input class="testo" id="fax_file" name="fax_file" type="file"></td></tr>
  <tr> <td class="button3"><img src="images/next2.png" class="invia3" title="Avanzate" onclick="Send_Fax();"><input class="fax2" type="image" src="images/send_fax.png" alt="[Submit]" name="submit" title= "Invia Fax"></td></tr>
 </table>
</div>
<div id="SendList2">
 <table class="headform7">
  <tr> <td colspan="4">&nbsp;</td></tr>
  <tr> <td colspan="2">Tempo di ritardo per l'invio<br>(vuoto=adesso)</td>
       <td><select name="s_day" id="s_day">
       <option value="Hour">Ore</option>
       <option value="Day">Giorni</option>
       </select> </td>
       <td><input type="text" size="5" maxlength="5" name="s_time" id="s_time"> </td></tr>
  <tr> <td colspan="2">Tempo massimo di invio<br>(vuoto= dopo 3 ore di tentativi)</td>
       <td><select name="k_day" id ="k_day">
       <option value="Hour">Ore</option>
       <option value="Day">Giorni</option>
       </select> </td>
       <td><input type="text" size="5" maxlength="5" name="k_time" id="k_time"></td><td></tr>
  <tr> <td>Qualit&agrave;:</td>
       <td><select name="quality" id ="quality">
       <option value="-m">Standard</option>
       <option value="-l">Bassa</option>
       </select></td>
       <td>Priorit&agrave;:</td>
       <td><select name="priority" id ="priority">
       <option value="normal">Normal</option>
       <option value="bulk">Junk</option>
       <option value="low">Low</option>
       <option value="high">High</option>
       </select></td></tr>
 </form>
 </table>
</div>

<div id="SendList3">
 <form name="send_multifax" enctype="multipart/form-data" method="post" target="uploadiframe" action="sendMultiFax.php" onsubmit="return true">
 <table class="headform2">
  <tr> <td class="button1">&nbsp;<span class="title">Invia Fax: Cerca i Destinatari</span><img src="images/Close.gif" class="chiudi" title="Chiudi" onclick="ClosePopup4();"></td></tr>
<? if ($GLOBALS['phonebook'] == 'none') { ?>
  <tr> <td>Cerca:&nbsp;<input class="testo" type="text" name="multicerca" id="multicerca" maxlength="20" disabled></td></tr>
  <tr> <td height="130px"><table><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td><font color="red" size="3pt">Nessuna Rubrica Selezionata</font></td></tr></table></td></tr>
<? } else { ?>
  <tr> <td>Cerca:&nbsp;<input class="testo" type="text" name="multicerca" id="multicerca" maxlength="20"></td></tr>
  <tr> <td id="SendList3_select"><select name="MultiInviaElenco" id="MultiInviaElenco" size="12" class="invia2" onDblClick="Select();">
       <option value="0">&nbsp;</option></select></td></tr>
<? } ?>
  <tr> <td>Invia Direttamente:&nbsp;<input class="testo" type="text" name="multifaxnumber" id="multifaxnumber" maxlength="15">&nbsp;&nbsp;<img src="images/add.png" class="aggiungi" title="Aggiungi invio diretto" onClick="Select2();"></td></tr>
  <tr> <td>Selezionati:<span id="numero2" name="numero2" class="numero2">&nbsp;&nbsp;</span></td></tr>
  <tr> <td><select name="selezione[]" id="selezione" size="12" multiple="multiple" class="selezione" onDblClick="deSelect();"> </select></td></tr>
  <tr> <td>Lista destinatari:&nbsp;&nbsp;<input class="testo" id="rec_file" name="rec_file" type="file"></td></tr>
  <tr> <td class="button3" colspan="2"><img src="images/next.png"  class="invia5" title="Avanti" onclick="Send_Fax3();"></td></tr>
 </table>
</div>
<div id="SendList4">
 <table class="headform2">
  <tr> <td class="button1" colspan="3">&nbsp;<span class="title">Invia Fax: Seleziona il Nome, il File da Inviare e le Modalit&agrave; di Invio</span><img src="images/Close.gif" class="chiudi" title="Chiudi" onclick="ClosePopup5();"></td></tr>
  <tr> <td colspan="3">Nome Invio Multiplo (obbligatorio):&nbsp;<input class="testo" type="text" name="description" id="description" maxlength="20"></td></tr>
  <tr> <td colspan="3">&nbsp;</td></tr>
  <tr> <td colspan="3">Sono supportati i formati pdf, tiff e postscript (ps).</td></tr>
  <tr> <td colspan="3">&nbsp;</td></tr>
  <tr> <td colspan="3">Seleziona il file da inviare:&nbsp;&nbsp;<input class="testo" id="multifax_file" name="multifax_file" type="file"></td></tr>
  <tr> <td colspan="3">&nbsp;</td></tr>
  <tr> <td>Tempo di ritardo per l'invio<br>(vuoto=adesso)</td>
       <td><select name="multis_day" id="multis_day">
       <option value="Hour">Ore</option>
       <option value="Day">Giorni</option>
       </select> </td>
       <td> <input type="text" size="5" maxlength="5" name="multis_time" id="multis_time"> </td></tr>
  <tr> <td>Tempo massimo di invio<br>(vuoto= dopo 3 ore di tentativi)</td>
       <td><select name="multik_day" id ="multik_day">
       <option value="Hour">Ore</option>
       <option value="Day">Giorni</option>
       </select> </td>
       <td> <input type="text" size="5" maxlength="5" name="multik_time" id="multik_time"></td><td></tr>
  <tr> <td> Qualit&agrave;:</td>
       <td colspan="2"><select name="multiquality" id ="multiquality">
       <option value="-m">Standard</option>
       <option value="-l">Bassa</option>
       </select> </td></tr>
  <tr> <td colspan="3" class="button3"><img src="images/previous.png" class="previous" title="Indietro" onclick="Previous2();">
       <input class="fax" type="image" src="images/send_multifax.png" alt="[Submit]" name="submit" title= "Invia Fax">
       </td></tr>
 </form>
 </table>
</div>

<div id="Address_book">
 <table class="headform">
 <form name="address">
  <tr> <td class="button3">&nbsp;<span class="title">Aggiungi a Rubrica FaxWeb</span><img src="images/Close.gif" class="chiudi2" title="Chiudi" onclick="ClosePopup3();"></td>
  </tr>
  <tr> <td>&nbsp;</td></tr>
  <tr> <td>Numero:&nbsp;&nbsp;<input id="fax_number" name="fax_number" class="testo" type="text"></td></tr>
  <tr> <td>&nbsp;</td></tr>
  <tr> <td>Nome:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="fax_address" name="fax_address" class="testo" type="text"></td></tr>
  <tr> <td>&nbsp;</td></tr>
  <tr> <td class="button3" ><img src="images/addressbook.png" class="invia2" title="Aggiungi" onclick="Add_address(this);"></td></tr>
 </form>
 </table>
</div>


<div id="advsearcharea">
 <form name="advsearch_form" enctype="multipart/form-data" method="post" onsubmit="return true">
 <table class="headform1">
  <tr> <td class="button1" colspan="2">&nbsp;<span class="title">Ricerca Avanzata: Configura i Parametri</span><img src="images/Close.gif" class="chiudi" title="Chiudi" onclick="ClosePopup7();"></td></tr>
  <tr><td>&nbsp;&nbsp;Ricerca in:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span name="search_folder" id="search_folder" class="search_folder"></span></td></tr>
  <tr><td>&nbsp;&nbsp;Includi sotto cartelle&nbsp;<input type="checkbox" name="search_check" id="search_check" value="1" checked="checked"/></td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;&nbsp;Numero Risultati:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="search_limit" id="search_limit">
           <option value="10">10</option>
           <option value="20">20</option>
           <option value="50">50</option>
           <option value="100">100</option>
           <option value="all">Tutti</option>
           </select>
      </td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;&nbsp;Tra il &nbsp;<input type="text" name="search_date_from" id="search_date_from">&nbsp;&nbsp;<img width="16" height="16" src="images/cal.gif" alt="Seleziona la data" title="Seleziona la data" id="search_date_from_img" class="data">
       <script type="text/javascript">
            Calendar.setup ({ inputField : "search_date_from", ifFormat : "%d-%m-%Y %H:%M:%S", showsTime : true, button : "search_date_from_img", singleClick : true, step : 1 })
       </script>
      &nbsp;&nbsp;&nbsp;&nbsp;e il &nbsp;&nbsp;&nbsp;<input type="text" name="search_date_to" id="search_date_to">&nbsp;&nbsp;<img width="16" height="16" src="images/cal.gif" alt="Seleziona la data" title="Seleziona la data" id="search_date_to_img" class="data">
       <script type="text/javascript">
            Calendar.setup ({ inputField : "search_date_to", ifFormat : "%d-%m-%Y %H:%M:%S", showsTime : true, button : "search_date_to_img", singleClick : true, step : 1 })
       </script>
      </td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;&nbsp;Nome:&nbsp;<input class="testo" type="text" name="search_name" id="search_name" maxlength="20" ></td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;&nbsp;Numero Fax:&nbsp;<input class="testo" type="text" name="search_number" id="search_number" maxlength="20" ></td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;&nbsp;Tag:&nbsp;<input class="testo" type="text" name="search_tag" id="search_tag" maxlength="20" ></td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>
      <div id="advsearch_inviati">
         &nbsp;&nbsp;Esito:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="search_esito" id="search_esito">
           <option value="all">Entrambi</option>
           <option value="ok">OK</option>
           <option value="ko">Con Problemi</option>
           </select>
      </div>
      <div id="advsearch_ricevuti">
         &nbsp;&nbsp;Letto:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="search_letto" id="search_letto">
           <option value="all">Entrambi</option>
           <option value="read">Letto</option>
           <option value="unread">Non Letto</option>
           </select>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inoltrati:&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="search_send" id="search_send">
           <option value="all">Entrambi</option>
           <option value="send">Inoltrati</option>
           <option value="unsend">Non Inoltrati</option>
           </select>
      </div>
     </td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr> <td class="button3"><img src="images/ricerca.png" class="invia4" title="Cerca" onclick="search.Advstart();"></td></tr>
 </table>
 </form>
</div>

<div>
	<div style="position:relative;">
		<div id="uploadcartclose" style="display:none; position:absolute; top:3px; right:5px; color:#333; cursor:pointer;" onclick="Element.toggle('uploadcart'); Element.toggle('uploadcartclose');"><img src="images/Close.gif" /></div>
	</div>  
	<div id="uploadcart" style="display:none;">
		<div id="uploadstatus"><em>Destinazione</em> scegli una cartella</div>
		
		<form id="uploadForm" name="uploadForm" method="post" action="." enctype="multipart/form-data" onsubmit="return false" target='uploadiframe'>
			<div id="uploadQ">
				<table cellspacing="0" cellpadding="0">
					<tbody id="uploadFiles"></tbody>
				</table>
			</div>
					
			<div id="uploadbuttons">
				<img src="images/addfile.jpg" class="addfile" id="uploadAdd" alt="choosefile" />
				<input id="fileUpload" class="fileupload" size="1" type="file" name="file[]" />
				<img class="upload" style="display:none" src="images/upload.jpg" id="uploadSubmit" onclick="uploadAuth();" />
                                <a class="invia" title="Invia Fax" href="#" onclick="uploadAuth();">invia</a>
			</div>
			
			<div id="progress" style="display:none;">
				<div id="pgbg"></div><div id="pgfg"></div>
				<span id="pgpc">0.0%</span>
				<span id="pgsp">0.0 K/s</span>
				<span id="pgeta">0</span>
			</div>
			<input type="hidden" name="faxweb" value="fileUpload" style="display:none;" />
			 <input type="hidden" name="path" id="uploadPath" />
		</form>	
		<iframe id="uploadiframe" name="uploadiframe">iframe</iframe>
	</div>
	<div style="position:relative;">
		<div id="downloadcartclose" style="display:none; position:absolute; top:3px; right:5px; color:#333; cursor:pointer;" onclick="Element.toggle('downloadcart'); Element.toggle('downloadcartclose');"><img src="images/Close.gif" /></div>
	</div>		
	<div id="downloadcart"  style="display:none;">
		
			<div id="cart">
				<div id="carthelper">Trascina un file qui</div>
			</div>
			<div id="cartbtn">
				<img src="images/emailto.jpg" class="emailto" onclick="cart.showEmail(); return false" alt="Email To" />
				<img src="images/download.jpg" class="downloadsubmit" onclick="cart.download(); return false" alt="Download Cart" />
		
				<table id="emailform" style="display:none;">
					<tr><td colspan="2">Invia via Email:</td></tr>
					<tr><td>A:</td><td><input type="text" id="emailFormTo" value="Scrivi l'indirizzo email" /></td></tr>
					<tr><td>Da:</td><td><input type="text" id="emailFormFrom" value="Scrivi il tuo indirizzo email" /></td></tr>
					<tr><td></td><td><textarea id="emailFormMessage">Scrivi il messaggio qui</textarea></td></tr>
					<tr><td> <td align="right"><img src="images/sendEmail.jpg" alt="Send Email" onclick="cart.download()" /></td></tr>
				</table>
				<div id="emailconfirm" style="display:none; text-align:center;">Email Spedita</div>
			</div>
	</div>  
</div>

<script type="text/javascript">
<!--
var auto_search  = new SearchCompleter('cerca', 'SendList_select', 'sendFax_completer.php');
-->
</script>

<script type="text/javascript">
<!--
var auto_multisearch  = new SearchCompleter('multicerca', 'SendList3_select', 'sendFax_completer.php');
-->
</script>
</body>
</html>
<?

} else {

      header("Location: index.php");
}
?>
