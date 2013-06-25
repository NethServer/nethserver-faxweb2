var Search = Class.create();

Search.prototype = {	

initialize: function(parent) {		
                               this.parentElement = $(parent);		
                               this.name = "Risultati Ricerca:";		
                               this.children = new Array();		
                               this.readonly = false;		
                               this.open = false;		
                               this.createSearch();	
},	
createSearch: function() {		
                               this.element = document.createElement('div');		
                               //this.span =    document.createElement('span');		
                               this.span =    document.createElement('div');		
                               this.link =    document.createElement('a');				
                               Element.addClassName(this.element, 'directory');		
                               Element.addClassName(this.element, 'search');		
                               //Element.addClassName(this.span, 'searchtitle');								
                               this.span.id= "searchtitle";
                               // create a generic spinner
                               this.spinner = document.createElement('img');		
                               this.spinner.src = spinnerIcon;		
                               this.spinner.style.display="none";		
                               Element.addClassName(this.spinner, 'search_spinner');			
                               // lets make the collapse expand indicator		
                               this.mark = document.createElement('img');		
                               this.mark.src = vcollapsed;		
                               Element.addClassName(this.mark, 'search_mark');				
                               this.link.href = "javascript:go();";		
                               this.link.innerHTML = this.name;		
                               this.display ? this.link.innerHTML = this.display : null;				
                               Element.addClassName(this.link, 'search_link');		
                               this.del = document.createElement('a');		
                               this.del.href = "javascript:go()";		
                               this.del.title = "Chiudi Ricerca";		
                               this.del.innerHTML = "Chiudi";		
                               Element.addClassName(this.del, 'search_del');		
                               // Events		
                               //this.mark.onclick = this.openOrClose.bind(this);					
                               //this.span.ondblclick = this.openOrClose.bind(this);		
                               this.del.onclick = this.hide.bind(this);		
                               this.link.onselectstart = function() {return false; }				
                               this.span.appendChild(this.link);		
                               this.span.appendChild(this.mark);		
                               this.span.appendChild(this.del);		
                               this.span.appendChild(this.spinner);		
                               this.parentElement.insertBefore(this.span,$('searchfiletitle'));									
                               //this.element.appendChild(this.span);									
                               this.element.id = "searchresults";		
                               this.element.style.height= document.body.parentNode.scrollHeight  - 143 + "px"; 			
                               this.element.object = this;		
                               this.element.style.display = "none";			
                               this.parentElement.appendChild(this.element);	
},		
openOrClose: function() {		
                               if(this.open) {			
                                              this.element.style.height="20px"; 			
                                              this.element.style.overflow = "hidden";			
                                              this.mark.src = vcollapsed;			
                                              this.open = false;		
                               } else {			
                                              this.open = true;			
                                              this.element.style.height="auto";
                                              this.mark.src = vexpanded;
                               }
},
show: function() {
                               this.element.style.display = "block";
},
hide: function() {
                               FC.SELECTEDMODE= 'normal';
                               $('searcharea').style.display= "none";
                               $('advsearcharea').style.display= "none";
                               $('search_folder').innerHTML= "";
                               this.totali.innerHTML="";
                               document.forms['advsearch_form'].search_check.checked=true;
                               document.forms['advsearch_form'].search_date_from.value='';
                               document.forms['advsearch_form'].search_date_to.value='';
                               document.forms['advsearch_form'].search_name.value='';
                               document.forms['advsearch_form'].search_number.value='';
                               document.forms['advsearch_form'].search_tag.value='';
                               document.forms['advsearch_form'].search_esito.selectedIndex = 0;
                               document.forms['advsearch_form'].search_letto.selectedIndex = 0;
                               document.forms['advsearch_form'].search_send.selectedIndex = 0;
                               document.forms['advsearch_form'].search_limit.selectedIndex = 0;
                               this.open=false;
                               Effect.Fade(this.element.id);
},
start: function(string) {
                               if(FC.SELECTEDDIRECTORY==null) {
                                   alert("Selezionare la cartella dove effettuare la ricerca."); 
                               } else if (FC.SELECTEDDIRECTORY.path== '/home/e-smith/faxweb/docs'){
                                   alert("Impossibile effettuare una ricerca in Faxweb, selezionare una sottocartella.");
                               } else if(FC.SELECTEDDIRECTORY.path!= '/home/e-smith/faxweb/docs') {

                               FC.SELECTEDMODE= 'search';

                               var htmlheight = document.body.parentNode.scrollHeight;
                               var htmlwidth = document.body.parentNode.scrollWidth;


                               $('searcharea').style.display= "inline";
                               $('searcharea').style.height = htmlheight - 93 + "px";
                               $('searcharea').style.width = htmlwidth - 320 + "px";


                               this.clearContents();
                               this.show();
                               this.mark.src = vexpanded;
                               var term = $('searchbar').value || string;
                               this.link.innerHTML = this.name + " <em>" + term + "</em>";
                               this.spinner.style.display = "block";
                               var params = $H({ faxweb: 'search', terms: term , path: FC.SELECTEDDIRECTORY.path});
                               //plusAjax();
                               var ajax = new Ajax.Request(FC.URL, {
                                          onSuccess: this.start_handler.bind(this),
                                          method: 'post',
                                          parameters: params.toQueryString(),
                                          onFailusre: function() {showError(ER.ajax); }
                               });
                               }
},
start_handler: function(response) {
                               this.open = true;
                               this.spinner.style.display = "none";
                               this.mark.src = vexpanded;
                               var json_data = response.responseText;
                               eval("var jsonObject = ("+json_data+")");
                               this.tot = jsonObject.bindings[0].tot;
                               this.view = jsonObject.bindings[0].view;

                               if(this.totali == null) this.totali = document.createElement('span');		
                               else Element.removeClassName(this.totali, 'search_results');
                               this.totali.innerHTML = "Trovati: " + this.tot + "&nbsp;&nbsp;&nbsp;&nbsp;Visualizzati: " + this.view;
                               Element.addClassName(this.totali, 'search_results');
                               this.span.appendChild(this.totali);
                               if(FC.SELECTEDDIRECTORY.path.match('received')) {
                               $('searchfiletitle').innerHTML = "<table class=\"headform4\"><tr><td class=\"titolo_ricerca\" width=\"50\">&nbsp;&nbsp;Etich.</td><td class=\"titolo_ricerca\" width=\"170\">&nbsp;Inviato da</td><td class=\"titolo_ricerca\" width=\"115\">&nbsp;Ricevuto il</td><td class=\"titolo_ricerca\" width=\"35\">&nbsp;View</td><td class=\"titolo_ricerca\" width=\"30\">&nbsp;Pg.</td><td class=\"titolo_ricerca\" width=\"180\">&nbsp;Inoltrato a</td><td class=\"titolo_ricerca\" width=\"65\">&nbsp;Dettagli</td><td class=\"titolo_ricerca\" width=\"55\">&nbsp;&nbsp;Azioni</td></tr></table>";
                               } else if (FC.SELECTEDDIRECTORY.path.match('sent')) {
                               $('searchfiletitle').innerHTML = "<table class=\"headform4\"><tr><td class=\"titolo_ricerca\" width=\"50\">&nbsp;&nbsp;Etich.</td><td class=\"titolo_ricerca\" width=\"170\">&nbsp;Destinazione</td><td class=\"titolo_ricerca\" width=\"115\">&nbsp;Data Invio</td><td class=\"titolo_ricerca\" width=\"35\">&nbsp;View</td><td class=\"titolo_ricerca\" width=\"30\">&nbsp;Pg.</td><td class=\"titolo_ricerca\" width=\"150\">&nbsp;Inviato da</td><td class=\"titolo_ricerca\" width=\"30\">&nbsp;Esito</td><td class=\"titolo_ricerca\" width=\"65\">&nbsp;Tentativi</td><td class=\"titolo_ricerca\" width=\"55\">&nbsp;&nbsp;Azioni</td></tr></table>";
                               }
                               for(var i=0; i < jsonObject.bindings.length; i++){

                                   var newFile = new File(jsonObject.bindings[i].id, jsonObject.bindings[i].name, jsonObject.bindings[i].date, jsonObject.bindings[i].rpath,jsonObject.bindings[i].flags, $('searchresults'), jsonObject.bindings[i].description, jsonObject.bindings[i].id_m, jsonObject.bindings[i].fax_type, jsonObject.bindings[i].number, jsonObject.bindings[i].sender, jsonObject.bindings[i].device, jsonObject.bindings[i].filename, jsonObject.bindings[i].sendto, jsonObject.bindings[i].msg, jsonObject.bindings[i].com_id, jsonObject.bindings[i].pages, jsonObject.bindings[i].duration, jsonObject.bindings[i].quality, jsonObject.bindings[i].rate, jsonObject.bindings[i].data, jsonObject.bindings[i].errcorr, jsonObject.bindings[i].page, jsonObject.bindings[i].resends, jsonObject.bindings[i].resend_rcp, jsonObject.bindings[i].forward_rcp, jsonObject.bindings[i].letto, jsonObject.bindings[i].doc_id, jsonObject.bindings[i].tts, jsonObject.bindings[i].ktime, jsonObject.bindings[i].rtime, jsonObject.bindings[i].job_id, jsonObject.bindings[i].state, jsonObject.bindings[i].user, jsonObject.bindings[i].attempts, jsonObject.bindings[i].esito, jsonObject.bindings[i].tipo, 'search');

                                   this.children.push(newFile);
                               } 
},
Advstart: function(string) {
                               FC.SELECTEDMODE= 'advsearch';
                               $('advsearcharea').style.display= "none";
                               var htmlheight = document.body.parentNode.scrollHeight;
                               var htmlwidth = document.body.parentNode.scrollWidth;

                               $('searcharea').style.display= "inline";
                               $('searcharea').style.height = htmlheight - 93 + "px";
                               $('searcharea').style.width = htmlwidth - 320 + "px";

                               var folder= 0;
                               if(document.forms['advsearch_form'].search_check.checked==true) folder=1;
                               else folder=0;
                               this.clearContents();
                               this.show();
                               this.mark.src = vexpanded;
                               //var term = $('searchbar').value || string;
                               var term = "Avanzata";
                               this.link.innerHTML = this.name + " <em>" + term + "</em>";
                               this.spinner.style.display = "block";
                               //plusAjax();
                               var params = $H({ faxweb: 'advSearch', limits: document.forms['advsearch_form'].search_limit.value, path: FC.SELECTEDDIRECTORY.path, check: folder, from: document.forms['advsearch_form'].search_date_from.value, to: document.forms['advsearch_form'].search_date_to.value, name: document.forms['advsearch_form'].search_name.value, number: document.forms['advsearch_form'].search_number.value, tag: document.forms['advsearch_form'].search_tag.value, esito: document.forms['advsearch_form'].search_esito.value, letto: document.forms['advsearch_form'].search_letto.value, send: document.forms['advsearch_form'].search_send.value });
                               var ajax = new Ajax.Request(FC.URL, {
                                          onSuccess: this.Adv_Search_handler.bind(this),
                                          method: 'post',
                                          parameters: params.toQueryString(),
                                          onFailure: function() { showError(ER.ajax); } });
},
Adv_Search_handler: function(response) {
                               this.open = true;
                               this.spinner.style.display = "none";
                               this.mark.src = vexpanded;
                               var json_data = response.responseText;
                               eval("var jsonObject = ("+json_data+")");
                               this.tot = jsonObject.bindings[0].tot;
                               this.view = jsonObject.bindings[0].view;

                               if(this.totali == null) this.totali = document.createElement('span');
                               else Element.removeClassName(this.totali, 'search_results');
                               this.totali.innerHTML = "Trovati: " + this.tot + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visualizzati: " + this.view;
                               Element.addClassName(this.totali, 'search_results');
                               this.span.appendChild(this.totali);
                               if(FC.SELECTEDDIRECTORY.path.match('received')) {
                               $('searchfiletitle').innerHTML = "<table class=\"headform4\"><tr><td class=\"titolo_ricerca\" width=\"50\">&nbsp;&nbsp;Etich.</td><td class=\"titolo_ricerca\" width=\"170\">&nbsp;Inviato da</td><td class=\"titolo_ricerca\" width=\"115\">&nbsp;Ricevuto il</td><td class=\"titolo_ricerca\" width=\"35\">&nbsp;View</td><td class=\"titolo_ricerca\" width=\"30\">&nbsp;Pg.</td><td class=\"titolo_ricerca\" width=\"180\">&nbsp;Inoltrato a</td><td class=\"titolo_ricerca\" width=\"65\">&nbsp;Dettagli</td><td class=\"titolo_ricerca\" width=\"55\">&nbsp;&nbsp;Azioni</td></tr></table>";
                               } else if (FC.SELECTEDDIRECTORY.path.match('sent')) {
                               $('searchfiletitle').innerHTML = "<table class=\"headform4\"><tr><td class=\"titolo_ricerca\" width=\"50\">&nbsp;&nbsp;Etich.</td><td class=\"titolo_ricerca\" width=\"170\">&nbsp;Destinazione</td><td class=\"titolo_ricerca\" width=\"115\">&nbsp;Data Invio</td><td class=\"titolo_ricerca\" width=\"35\">&nbsp;View</td><td class=\"titolo_ricerca\" width=\"30\">&nbsp;Pg.</td><td class=\"titolo_ricerca\" width=\"150\">&nbsp;Inviato da</td><td class=\"titolo_ricerca\" width=\"30\">&nbsp;Esito</td><td class=\"titolo_ricerca\" width=\"65\">&nbsp;Tentativi</td><td class=\"titolo_ricerca\" width=\"55\">&nbsp;&nbsp;Azioni</td></tr></table>";
                               }
                               for(var i=0; i < jsonObject.bindings.length; i++){

                                   var newFile = new File(jsonObject.bindings[i].id, jsonObject.bindings[i].name, jsonObject.bindings[i].date, jsonObject.bindings[i].rpath,jsonObject.bindings[i].flags, $('searchresults'), jsonObject.bindings[i].description, jsonObject.bindings[i].id_m, jsonObject.bindings[i].fax_type, jsonObject.bindings[i].number, jsonObject.bindings[i].sender, jsonObject.bindings[i].device, jsonObject.bindings[i].filename, jsonObject.bindings[i].sendto, jsonObject.bindings[i].msg, jsonObject.bindings[i].com_id, jsonObject.bindings[i].pages, jsonObject.bindings[i].duration, jsonObject.bindings[i].quality, jsonObject.bindings[i].rate, jsonObject.bindings[i].data, jsonObject.bindings[i].errcorr, jsonObject.bindings[i].page, jsonObject.bindings[i].resends, jsonObject.bindings[i].resend_rcp, jsonObject.bindings[i].forward_rcp, jsonObject.bindings[i].letto, jsonObject.bindings[i].doc_id, jsonObject.bindings[i].tts, jsonObject.bindings[i].ktime, jsonObject.bindings[i].rtime, jsonObject.bindings[i].job_id, jsonObject.bindings[i].state, jsonObject.bindings[i].user, jsonObject.bindings[i].attempts, jsonObject.bindings[i].esito, jsonObject.bindings[i].tipo,'advsearch');

                                   this.children.push(newFile);
                               }
},
clearContents: function () {
                               this.open = false;
                               while(this.children.length > 0) { this.removeChild(this.children[0], 0);}
},
removeChild: function (child, index) {
                               if(!index) {
                                           for(var i=0; i< this.children.length; i++) {
                                                              if(this.children[i] == child){ var index = i; break; }
                                           }
                                }
                               this.children.splice(index, 1);
                               // remove my droppable
                               Element.remove(child.element);
                               // and finally get rid of me
                               delete child;	
}
};
function monitorSearch(event) {
                               alert(event);
                               var charCode = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode);
                               switch(charCode) {
                                                   case Event.KEY_RETURN:
                                                                          doSearch();
                                                                          break;
                                                }
}
