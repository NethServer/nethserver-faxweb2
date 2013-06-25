
var FC = {
URL: 'faxweb.php',
TYPES: new Array('file','directory'),
SELECTEDOBJECT: null,
SELECTEDDIRECTORY: null,
SELECTEDPAGE: 1,
SELECTEDTOTPAGE: 1,
SELECTEDMODE: 'normal',
SHOWALL: false,
SCRIPTSRC: location.href,
SEARCHOBJ: null,
NEXTPATH: null,
AJAXCALL: 0,
UPLOADURL: 'upload.pl',
DEBUG:false
};

var Directory = Class.create();
Directory.prototype = {

initialize: function (path, name, flag, parentElement, virtual, scheme, displayname) {
	this.path = path;
	this.type = 'directory';
	if(name=='received') name='Ricevuti';
	if(name=='sent') name='Inviati';
	if(name=='sentm') name='Invii Multipli';
	this.name = name;
	this.id = this.path;
	this.flag = flag;
	this.virtual = virtual || false;
	if(name=='docs') this.open = true;
	else this.open = false;
	this.selected = false;
	this.changed = false;
	this.timer = null;
	this.interval = 1000;
	this.children = new Array();
	
	this.path == '' ? this.isRoot = true : this.isRoot = false;
	this.parentElement = parentElement;
	this.parentElement.object ? this.parentObject = this.parentElement.object :null;
	if(scheme) this.readonly = (scheme == 'read');
	else if(this.parentObject) this.readonly = this.parentObject.readonly;
	else this.readonly = true;
	displayname ? this.display = displayname: null;	
	if(name!='tmp' && name!='thumb') this.createDirectory();
	FC.SHOWALL || this.virtual == "true" ? this.getContents() : this.virtual == "closed" ? this.virtual = true : null;
},

createDirectory: function () {
	this.element = document.createElement('div');
	this.link =    document.createElement('a');
	this.icon = document.createElement('img');
	this.handle = document.createElement('div');
	Element.addClassName(this.handle, 'handle');

	this.spinner = document.createElement('img');
	this.spinner.src = spinnerIcon;
	this.spinner.style.display="none";
	Element.addClassName(this.spinner, 'spinner');
	
	this.span =    document.createElement('span');
	this.icon.src = folderIcon;
	Element.addClassName(this.icon, 'icon');
	
	this.mark = document.createElement('img');
	this.virtual ? this.mark.src = vcollapsed : this.mark.src = collapsed;
	Element.addClassName(this.mark, 'mark');
	
	this.link.href = "javascript:go();";
	this.link.title = this.id;
	this.link.innerHTML = this.name;
	this.display ? this.link.innerHTML = this.display : null;
	
	Element.addClassName(this.link, 'link');

	//this.del = document.createElement('a');
	//this.del.href = "javascript:go()";
	//this.del.innerHTML = "elimina";
	//this.del.style.display = "none";
	//Element.addClassName(this.del, 'del');
	
	this.note = document.createElement('span');
	this.note.innerHTML = '(read-only)';
	Element.addClassName(this.note, 'note');

	// Events
        this.mark.onclick = this.openOrClose.bindAsEventListener(this);
        this.span.onclick = this.select.bindAsEventListener(this);
       
	//!this.readonly ? this.del.onclick = this.unlink.bindAsEventListener(this) : null;
	this.link.onselectstart = function() {return false; }
	this.handle.appendChild(this.icon);
	this.handle.appendChild(this.link);
	this.span.appendChild(this.mark);
	this.span.appendChild(this.handle);
	
	if(this.readonly) this.span.appendChild(this.note);
	//if(!this.virtual && !this.readonly ) {
	//	this.span.appendChild(this.del);
	//}
	this.span.appendChild(this.spinner);
	this.element.appendChild(this.span);
			
	if (this.isRoot) this.span.style.display = "none";
	
	this.element.id = "root" + this.id;
	this.element.object = this;
	
	Element.addClassName(this.element, this.type);
	if(this.virtual) {
		Element.addClassName(this.element, 'virtual');
		Element.addClassName(this.span, 'virtual');
	}
	if(this.readonly) {
		Element.addClassName(this.element, 'read');
		Element.addClassName(this.span, 'read');
	}				
	this.parentElement.appendChild(this.element);	
	if(!this.isRoot && !this.readonly){
		if(!this.virtual) new Draggable(this.element.id, {revert:true, handle:'handle'});
		Droppables.add(this.element.id, { accept: FC.TYPES, hoverclass: 'hover', onDrop: this.moveTo.bind(this) });
		this.resetHierarchy();
	}
	
 },
 
getContents: function () {
	if(this.opening) return false;
	this.opening = true;
	var params = $H({ faxweb: 'getFolder', path: this.path, page: FC.SELECTEDPAGE });
	this.showActivity();
	var ajax = new Ajax.Request(FC.URL, {
		onSuccess: this.getContents_handler.bind(this),
		method: 'post', 
		parameters: params.toQueryString(), 
		onFailure: function() { showError(ER.ajax); }
	});
},

getFiles: function () {
        if(this.opening) return false;
        this.opening = true;
        var params = $H({ faxweb: 'getFolder', path: this.path, page: FC.SELECTEDPAGE });
        this.showActivity();
        var ajax = new Ajax.Request(FC.URL, {
                onSuccess: this.getFiles_handler.bind(this),
                method: 'post',
                parameters: params.toQueryString(),
                onFailure: function() { showError(ER.ajax); }
        });
},

getFolders: function () {
	if(this.opening) return false;
	this.opening = true;
	var params = $H({ faxweb: 'getFolder', path: this.path, page: FC.SELECTEDPAGE });
	this.showActivity();
	var ajax = new Ajax.Request(FC.URL, {
		onSuccess: this.getFolders_handler.bind(this),
		method: 'post',
		parameters: params.toQueryString(),
		onFailure: function() { showError(ER.ajax); }
	});
},

getContents_handler: function (response) {
	Element.addClassName(this.span, 'open');
	this.opening = false;
	this.virtual ? this.mark.src = vexpanded : this.mark.src = expanded;
	this.hideActivity();
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings.length == 0) { this.addBlank(); return true; }
	if(jsonObject.bindings[0].error) this.parentObject.update();
	this.clearContents();
	file_list.clearContents();

	for(var i=0; i < jsonObject.bindings.length; i++) {
		if (this.open && jsonObject.bindings[i].type == 'directory') this.addChild(jsonObject.bindings[i]);
		else if (jsonObject.bindings[i].type != 'directory') file_list.addChild(jsonObject.bindings[i]);
	}

	if (this.andPick && i > 0) this.children[this.andPick].select();
	if (FC.NEXTPATH && !this.isRoot) { parsePath(FC.NEXTPATH); }

},

getFiles_handler: function (response) {
        Element.addClassName(this.span, 'open');
        this.opening = false;
        this.virtual ? this.mark.src = vexpanded : this.mark.src = expanded;
        this.hideActivity();
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings.length == 0) { this.addBlank(); return true; }
        if(jsonObject.bindings[0].error) this.parentObject.update();
        file_list.clearContents();

        for(var i=0; i < jsonObject.bindings.length; i++) {
                 if (jsonObject.bindings[i].type != 'directory') file_list.addChild(jsonObject.bindings[i]);
        }

        if (this.andPick && i > 0) this.children[this.andPick].select();
        if (FC.NEXTPATH && !this.isRoot) { parsePath(FC.NEXTPATH); }

},


getFolders_handler: function (response) {
	Element.addClassName(this.span, 'open');
	this.opening = false;
	this.virtual ? this.mark.src = vexpanded : this.mark.src = expanded;
	this.hideActivity();
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	this.clearContents();

	for(var i=0; i < jsonObject.bindings.length; i++) {
		if (this.open && jsonObject.bindings[i].type == 'directory') this.addChild(jsonObject.bindings[i]);
	}

},

update: function () {
	if (this.open || FC.SELECTEDDIRECTORY == this) {
		var params = $H({ faxweb: 'getFolder', path: this.path, page: FC.SELECTEDPAGE });
		this.showActivity();
		var ajax = new Ajax.Request(FC.URL,{
			onSuccess : this.update_handler.bind(this),
			method: 'post',
			parameters: params.toQueryString(),
			onFailure: function() { showError(ER.ajax); }
		});
	}
	else this.getContents();
},

update_handler: function(response) {
	this.hideActivity();
	var json_data = response.responseText; 
	eval("var jsonObject = ("+json_data+")");		
	if(jsonObject.bindings.length > 0) { this.removeBlank(); } else this.addBlank();

	for(var i=0; i < this.children.length; i++) {
		var found = false;			
		for (var j=0; j < jsonObject.bindings.length; j++) {
			if(this.children[i].id == jsonObject.bindings[j].id || this.children[i].id == jsonObject.bindings[j].path) {
				found = true;
				break;
			}
		}

		if (jsonObject.bindings[j].type != 'directory') continue;
		if (found) {
			     jsonObject.bindings.splice(j, 1);
		} else {
			this.removeChild(this.children[i], i);
			i--;
		}
	}

	for (var k=0; k < jsonObject.bindings.length; k++) { 
		if (jsonObject.bindings[k].type == 'directory') {
			this.addChild(jsonObject.bindings[k]);
		}
	}
	if (this.andPick && i > 0) this.children[this.andPick].select(); 
	if (FC.NEXTPATH) { parsePath(FC.NEXTPATH); }
	if(FC.SELECTEDOBJECT != null && FC.SELECTEDDIRECTORY != this) return;
	for(var i=0; i < file_list.children.length; i++) {
		var found = false;

		for (var j=0; j < jsonObject.bindings.length; j++) {
			if(file_list.children[i].id == jsonObject.bindings[j].id) {
				found = true;
				break;
			}
		}
		if (found){
			    file_list.children[i].msg = jsonObject.bindings[j].msg;
			    file_list.children[i].flag = jsonObject.bindings[j].flag;
			    file_list.children[i].rpath = jsonObject.bindings[j].rpath;
			    file_list.children[i].esito = jsonObject.bindings[j].esito;
			    file_list.children[i].tipo = jsonObject.bindings[j].tipo;
			    file_list.children[i].sender = jsonObject.bindings[j].sender;
			    file_list.children[i].letto = jsonObject.bindings[j].letto;
			    file_list.children[i].esito = jsonObject.bindings[j].esito;
			    file_list.children[i].state = jsonObject.bindings[j].state;
			    file_list.children[i].attempts = jsonObject.bindings[j].attempts;
			    file_list.children[i].forward_rcp = jsonObject.bindings[j].forward_rcp;
			    file_list.children[i].resends = jsonObject.bindings[j].resends;
			    file_list.children[i].resend_rcp = jsonObject.bindings[j].resend_rcp;
			    file_list.children[i].name = jsonObject.bindings[j].name;
			    file_list.children[i].date = jsonObject.bindings[j].date;
			    file_list.children[i].pages = jsonObject.bindings[j].pages;
			    file_list.children[i].flag = jsonObject.bindings[j].flags;
			    file_list.children[i].job_id = jsonObject.bindings[j].job_id;
			    file_list.children[i].refresh();
			    jsonObject.bindings.splice(j, 1); }
		else {
			file_list.removeChild(file_list.children[i], i);
			i--;
		}
	}

	for (var k=0; k < jsonObject.bindings.length; k++) {
		if (jsonObject.bindings[k].type != 'directory') {
			file_list.addChild(jsonObject.bindings[k]);
		}
	}
},
	
openOrClose: function () {
	this.open = this.open ? false : true;
	this.getContents();
},

resetHierarchy: function () {
	if (this.parentObject.type == "directory" && this.parentObject.isRoot == false) {
		Droppables.remove(this.parentElement);
		Droppables.add(this.parentElement.id, { accept: FC.TYPES, hoverclass: 'hover', onDrop: this.parentObject.moveTo.bind(this.parentObject) });
		this.parentObject.resetHierarchy();
	}
},
	
clearContents: function () {
	this.removeBlank();
	while(this.children.length > 0) {
		(this.children[0].type == 'directory' && this.children[0].hasChildren()) ? this.children[0].clearContents() : this.removeChild(this.children[0], 0);
	}
	Element.removeClassName(this.span, 'open');
	this.virtual ? this.mark.src = vcollapsed : this.mark.src = collapsed;
},

removeChild: function (child, index) {
	if(!index) {
		for(var i=0; i< this.children.length; i++) {
			if(this.children[i] == child){ var index = i; break; }
		}
	}
	this.children.splice(index, 1);
	if(child.type == 'directory') Droppables.remove(child.id);
	Element.remove(child.element);
},

clear: function () { this.parentObject.removeChild(this); },

addChild:  function (child) {
        if (child.type == 'file') {
	               var newFile = new File(child.id, child.name, child.date, child.rpath, child.flags, this.element, child.description, child.id_m, child.fax_type, child.number, child.sender, child.device, child.filename, child.sendto, child.msg, child.com_id, child.pages, child.duration, child.quality, child.rate, child.data, child.errcorr, child.page, child.resends, child.resend_rcp, child.forward_rcp, child.letto, child.doc_id, child.tts, child.ktime, child.rtime, child.job_id, child.state, child.user, child.attempts, child.esito, child.tipo);
	               this.children.push(newFile);
        } else if (child.type == 'directory') {
	               var newDir = new Directory(child.path, child.name, child.flags, this.element, child.virtual, child.scheme, child.displayname);			
                       this.children.push(newDir);
        } else return 0;
},

moveTo: function (element) { 
        Element.removeClassName(this.element, 'hover');
        if (element.object.parentObject == this) { return false; }
        if ( element.object.type == 'directory' ) {
	        var params = $H({ 
		                 faxweb: 'folderMove', 
                 		 name: element.object.name, 
		                 path: element.object.parentObject.path, 
 		                 newpath: this.path
	                      });
	        FC.SEARCHOBJ = this;
	        FC.NEXTPATH = '/'+ element.object.name;
	
	        element.object.clearContents();	
	        element.object.clear();
	        var ajax = new Ajax.Request(FC.URL, {
		                                      onSuccess: this.moveTo_handler.bind(this),  
		                                      method: 'post', 
		                                      parameters: params.toQueryString(), 
		                                      onFailure: function() { showError(ER.ajax); }
	                                             });

        } else {
	        var params = $H({ 
		                  faxweb: 'fileMove', 
		                  fileid: element.object.id, 
		                  path: this.path 
	                        });
	        FC.SEARCHOBJ = this;
	        FC.NEXTPATH = '/'+ element.object.name;
	        if(!element.object.search) { element.object.clear(); }
	        var ajax = new Ajax.Request(FC.URL,{
		                                    onSuccess: this.moveTo_handler.bind(this),
		                                    method: 'post', 
		                                    parameters: params.toQueryString(), 
		                                    onFailure: function() { showError(ER.ajax); }
	                                           });
        }		
				
},

moveTo_handler: function(response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(FC.SELECTEDMODE=='advsearch') search.Advstart();
        else if (FC.SELECTEDMODE=='search') search.start();
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
},

select: function (event) {
        $('uploadPath').value = this.path;
        if(this.path.match('received')) {
                                          $('FileTitle').innerHTML = "<table class=\"headform4\"><tr><td class=\"titolo_file\" width=\"50\">&nbsp;&nbsp;Etich.</td><td class=\"titolo_file\" width=\"170\">&nbsp;Inviato da</td><td class=\"titolo_file\" width=\"115\">&nbsp;Ricevuto il</td><td class=\"titolo_file\" width=\"35\">&nbsp;View</td><td class=\"titolo_file\" width=\"30\">&nbsp;Pg.</td><td class=\"titolo_file\" width=\"180\">&nbsp;Inoltrato a</td><td class=\"titolo_file\" width=\"65\">&nbsp;Dettagli</td><td class=\"titolo_file\" width=\"55\">&nbsp;&nbsp;Azioni</td></tr></table>";
        } else if (this.path.match('sent')) {
                                              $('FileTitle').innerHTML = "<table class=\"headform4\"><tr><td class=\"titolo_file\" width=\"50\">&nbsp;&nbsp;Etich.</td><td class=\"titolo_file\" width=\"170\">&nbsp;Destinazione</td><td class=\"titolo_file\" width=\"115\">&nbsp;Data Invio</td><td class=\"titolo_file\" width=\"35\">&nbsp;View</td><td class=\"titolo_file\" width=\"30\">&nbsp;Pg.</td><td class=\"titolo_file\" width=\"150\">&nbsp;Inviato da</td><td class=\"titolo_file\" width=\"30\">&nbsp;Esito</td><td class=\"titolo_file\" width=\"65\">&nbsp;Tentativi</td><td class=\"titolo_file\" width=\"55\">&nbsp;&nbsp;Azioni</td></tr></table>";
        }

        $('uploadstatus').innerHTML = "<em>Destinazione</em> "+this.path;
        if(FC.SELECTEDDIRECTORY != null && FC.SELECTEDDIRECTORY != this) FC.SELECTEDDIRECTORY.deselect(); 
        PageCount(this.path);
//        window.onkeypress = this.select_handler.bindAsEventListener(this);
        FC.SELECTEDOBJECT = this;		
        FC.SELECTEDDIRECTORY = this;
        $('pagine').style.display= "inline";
        Element.addClassName(this.span, 'selected');
        this.getFiles();

        if ($('meta').prevElement != this.path ) this.getMeta();
        return false;
},

select_handler: function (event) {
        var charCode = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode);
        switch(charCode) {
	                  case Event.KEY_DOWN:
               		       this.parentObject.nextChild(this);
		               break;
			  case Event.KEY_UP:
		               this.parentObject.prevChild(this);
			       break;
  			  case Event.KEY_RIGHT:
 			       if(this.open) this.children[0].select(); 
		               else { this.openOrClose(); this.andPick = '0'; }
		               break;
	                  case Event.KEY_LEFT:
		               this.parentObject.select(this);
		               break;
	                 }
},

deselect: function () {
	this.timer = null;
	window.onkeypress = null;
	//this.del.style.display = 'none';
	Element.removeClassName(this.span, 'selected');
        FC.SELECTEDPAGE= 1;
        $('pageNumber').innerHTML = "";
        $('pagine').style.display= "none";
	this.selected = false; 
	this.clearRename();
},

getMeta: function () {
	$('meta').prevElement = this.path;
	var params = $H({ faxweb: 'getFolderMeta', path: this.path });
	var ajax = new Ajax.Request(FC.URL,{
						onLoading: showMetaSpinner(), 
						onSuccess: this.getMeta_handler.bind(this),
						method: 'post', 
						parameters: params.toQueryString(), 
						onFailure: function() { showError(ER.ajax); }
					   });
},

getMeta_handler: function(response) {
	var json_data = response.responseText;		
	eval("var jsonObject = ("+json_data+")");
	if (jsonObject.bindings.length >= 1) {
						var meta = { name: jsonObject.bindings[0].name, size: jsonObject.bindings[0].size, numero: jsonObject.bindings[0].numero, inviati: jsonObject.bindings[0].inviati, errori: jsonObject.bindings[0].errori, ritrasmessi: jsonObject.bindings[0].ritrasmessi, attesa: jsonObject.bindings[0].attesa, desc: jsonObject.bindings[0].desc,path: this.path};
	                                        updateMeta(meta);
	} else updateMeta({ '<img src="/directory/sad.gif" />':'No Info to display'});	
},

nextChild: function(child) {
	var pos = this.checkIfChild(child);
	if(pos != this.children.length-1) this.children[pos+1].select();
	else if(!this.isRoot) this.parentObject.nextChild(this);
	},
prevChild: function(child) {
	var pos = this.checkIfChild(child);
	if(pos != 0) this.children[pos-1].select();
	else if(pos == 0 && !this.isRoot) this.select();
},

checkIfChild: function (child) {
	if(this.hasChildren()){
				for (var i=0;i < this.children.length;i++){
				if(this.children[i].id == child.id) return i;
	} 
	return false;
	} else return false;
},

clearRename: function () {
	if(this.renameIsOpen) {
				Element.remove(this.newName);
				this.link.style.display = "block";
				this.renameIsOpen = false;
	}
},

rename_handler: function (event, direct) {
	if(!direct) { var charCode = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode); 
	if (charCode == Event.KEY_ESC) this.clearRename(); }
	if (charCode == Event.KEY_RETURN || direct) {
		var params = $H({ faxweb : 'folderRename', path  : this.parentObject.path, name  : this.name, newname: direct || this.newName.value });
 	        this.clearRename();
				var ajax = new Ajax.Request(FC.URL,{ 
									onComplete: this.select.bind(this),
									onSuccess: this.renameFolder_handler.bind(this),
									method: 'post', 
									parameters: params.toQueryString(), 
									onFailure: function() { showError(ER.ajax); }
								    });
	}		
},

renameFolder_handler: function (response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error)  alert(jsonObject.bindings[0].error);
        else { 
		this.link.innerHTML = jsonObject.bindings[0].name;
                this.id = jsonObject.bindings[0].newpath;
                this.path = jsonObject.bindings[0].newpath;
                this.link.title = this.id;
                this.parentObject.update.bind(this.parentObject);
        }
},


showRename: function () {
	if(this.readonly) return false;
	if(this.virtual) return false;
	this.renameIsOpen = true;
	this.newName = document.createElement('input');
	this.newName.type = 'text';
	this.newName.name = this.id;
	this.newName.value = this.name;
	window.onkeypress = this.rename_handler.bindAsEventListener(this);
	Element.addClassName(this.newName, 'renamefield');
	this.link.style.display = "none";
	this.span.appendChild(this.newName);
	Field.select(this.newName);
},

showActivity: function () { 
	this.spinner.style.display = "block";
},

hideActivity: function () { 
	this.spinner.style.display = "none";
},

hasChildren: function () {
	if (this.children.length > 0) return true;
	else return false;
},

unlink: function () {
	if(this.readonly) return false;
	if(this.virtual) return false;

	if (this.path=='/home/e-smith/faxweb/docs/sent' || this.path=='/home/e-smith/faxweb/docs/sentm' || this.path=='/home/e-smith/faxweb/docs/received') {
		   alert("Impossibile eliminare questa Cartella.");
	} else {

	if(confirm('Eliminare la Cartella '+this.name+ '?')) {

					var params = $H({ faxweb: 'folderDelete', folder: this.path });
				//	this.parentObject.prevChild(this);
					var ajax = new Ajax.Request(FC.URL,{
										onComplete: root.select(), 
										onSuccess: this.clear.bind(this),    
										method: 'post', 
										parameters: params.toQueryString(), 
										onFailure: function() { showError(ER.ajax); }
									   });
	} }
},

addBlank: function() {
	file_list.clearContents();
	if(this.blankisshowing) return false;
	this.blankisshowing = true;
},

removeBlank: function() {
	if(this.blankisshowing) {
		this.blankisshowing = false;
	}
}

};

var File = Class.create();
File.prototype = {

initialize: function (id, name, date, rpath, flags, parentElement, description, id_m, fax_type, number, sender, device, filename, sendto, msg, com_id, pages, duration, quality, rate, data, errcorr, page, resends, resend_rcp, forward_rcp, letto, doc_id, tts, ktime, rtime, job_id, state, user, attempts, esito, tipo) {

	this.type = 'file';
	this.fileDate = date;
	this.name = name;
	this.id = id;
	this.rpath = rpath;
	this.flag = flags;
	this.fax_id = id;
	this.fax_type = fax_type;
	this.number = number;
	this.sender = sender; 
	this.device = device; 
	argomento = name.split(".");
	this.fax_filename = argomento[0]; 
	this.sendto = sendto; 
	this.msg = msg; 
	this.com_id = com_id; 
	this.fax_date = date; 
	this.pages = pages; 
	this.duration = duration; 
	this.quality = quality; 
	this.rate = rate; 
	this.data = data; 
	this.errcorr = errcorr; 
	this.page = page; 
	this.resends = resends; 
	this.resend_rcp = resend_rcp; 
	this.forward_rcp = forward_rcp; 
	this.letto = letto; 
	this.doc_id = doc_id;
	this.tts = tts; 
	this.ktime = ktime; 
	this.rtime = rtime; 
	this.job_id = job_id; 
	this.state = state; 
	this.user = user;
	this.attempts = attempts; 
	this.esito = esito; 
	this.tipo = tipo;
	this.id_m= id_m;
	this.description = description;
	this.selected = false;
	this.timer = null;
	this.interval = 1000;
	this.parentElement = parentElement;
	this.parentObject = parentElement.object;
	this.readonly = this.parentObject.readonly;
	this.parentElement.id == 'searchresults' ? this.search = true : this.search = false;
	this.createFile();
},

createFile: function () { 
        var htmlheight = document.body.parentNode.scrollHeight;
	if ( this.fax_type =='R') {
				this.element = document.createElement('div');
				this.span =    document.createElement('span');
				this.link =    document.createElement('a');
				this.ante =    document.createElement('a');
				this.readfax = document.createElement('a');
				this.dett =    document.createElement('a');
				this.inoltro = document.createElement('a');
				this.icon   =  document.createElement('img');
				this.img_fax = document.createElement('img');
				this.abook  =  document.createElement('img');
				this.handle =  document.createElement('div');
				Element.addClassName(this.handle, 'handle');

				this.flag != 'normal' ? this.icon.src = "images/"+this.flag+".png" : this.icon.src= fileIcon;
				Element.addClassName(this.icon, 'icon2');

				if (this.letto=='0') { this.readfax.innerHTML = "<img src='images/nonletto.png'>"; 
						       this.readfax.title = "";
						       Element.addClassName(this.readfax, 'read');
				} else { this.readfax.innerHTML = "<img src='images/letto.png'>";
						       this.readfax.title = this.letto;
						       Element.addClassName(this.readfax, 'read2'); }

				if (!this.sender && !this.number) { this.link.innerHTML="Sconosciuto";
                                                                    if(FC.SELECTEDMODE=='advsearch') this.link.title = this.rpath.replace(/received/, "Ricevuti");
                                } else  if (this.sender!= '')  { this.link.innerHTML = this.sender.substring(0,20); 
								 if(FC.SELECTEDMODE!='advsearch') this.link.title = this.number;
                                                                 else this.link.title = this.rpath.replace(/received/, "Ricevuti"); }
				else { this.link.innerHTML = this.number;
                                       if(FC.SELECTEDMODE=='advsearch') this.link.title = this.rpath.replace(/received/, "Ricevuti"); }
				if (this.letto=='0') Element.addClassName(this.link, 'link3');
				else Element.addClassName(this.link, 'link2');	
	
				this.abook.title = "Aggiungi in Rubrica FaxWeb";
				this.abook.src = "images/freccia_avanti.gif" ;
				Element.addClassName(this.abook, 'abook');
	
				this.ante.innerHTML = "<img src='images/view.png'>"; 
				this.ante.onmouseover = 
                                function() {
                                            $('preview').innerHTML="<span>" + this.rpath.replace(/received/, "Ricevuti") + "<br><br><img src='faxAnt.php?id="+this.rpath+'/'+ this.fax_filename + "-15.png' height=280 width=600> </span>";
                                            $('preview').style.display= "inline"; 
                                           }.bind(this);
				this.ante.onmouseout =  function() { $('preview').innerHTML='';
                                                                     $('preview').style.display= "none"; };
				Element.addClassName(this.ante, 'ante');

				this.faxdate = document.createElement('span');
				this.faxdate.innerHTML = this.fax_date;
				if (this.letto=='0') Element.addClassName(this.faxdate, 'date2');
				else Element.addClassName(this.faxdate, 'date');	

				this.pagine = document.createElement('span');
				this.pagine.innerHTML = this.pages;
				if (this.letto=='0') Element.addClassName(this.pagine, 'pagine2');
				else  Element.addClassName(this.pagine, 'pagine');

                                if( this.forward_rcp == '') {
                                                                this.inoltro.innerHTML = "";
                                } else {
                                        this.inoltro.innerHTML = this.forward_rcp.split("(",1) + "...";
                                        this.inoltro.onmouseover =
                                        function(e) {
                                                     var IE = document.all?true:false
                                                     var tempX = 0
                                                     var tempY = 0
                                                     if (IE) {
                                                              tempX = event.clientX + document.body.scrollLeft
                                                              tempY = event.clientY + document.body.scrollTop
                                                     } else {
                                                              tempX = e.pageX
                                                              tempY = e.pageY
                                                     }
                                                     if (tempX < 0){tempX = 0} 
                                                     if (tempY < 0){tempY = 0}
                                                     $('inoltri').innerHTML="<span>"+  this.forward_rcp + "</span>";
                                                     $('inoltri').style.left= tempX - 380 + "px";
                                                     var verticale = tempY - 30;
                                                     if( verticale > ( htmlheight - 65 )) $('inoltri').style.top = htmlheight - 65 + "px";
                                                     else $('inoltri').style.top = tempY - 30 + "px";
                                                     $('inoltri').style.display= "inline";
                                                   }.bind(this);
                                        this.inoltro.onmouseout =  function() { $('inoltri').innerHTML='';
                                                                                $('inoltri').style.display= "none"; };
                                }
				if (this.letto=='0') Element.addClassName(this.inoltro, 'inoltro2');
				else Element.addClassName(this.inoltro, 'inoltro');

				if (this.msg != '') {  this.img_fax.title = "Problemi nella ricezione del fax";
						       this.img_fax.src = "images/important.png" ;
				} else { this.img_fax.title = "Fax ricevuto correttamente";
					 this.img_fax.src= "images/ledgreen.png";
				}
				Element.addClassName(this.img_fax, 'img_fax');

				this.dett.innerHTML = "|-|";
                                this.dett.onmouseover =
                                function(e) {
                                             var IE = document.all?true:false
                                             var tempX = 0
                                             var tempY = 0
                                             if (IE) {
                                                      tempX = event.clientX + document.body.scrollLeft
                                                      tempY = event.clientY + document.body.scrollTop
                                             } else {
                                                      tempX = e.pageX
                                                      tempY = e.pageY
                                             }
                                             if (tempX < 0){tempX = 0}
                                             if (tempY < 0){tempY = 0}

                                             $('rec_dett').innerHTML="<span>File Name : " + this.name + "<br>Msg : " + this.msg + "<br>Signal Rate : " + this.rate + "<br>Device : " + this.device + "<br>Send To: " + this.sendto + "<br>Com ID: " + this.com_id + "<br>Quality : " + this.quality + "<br>Data : " + this.data + "<br>Err. Corr.: " + this.errcorr + "<br>Page : "+ this.page + "<br>Durata : " + this.duration;
                                             $('rec_dett').style.left = tempX - 220 + "px";
                                             var verticale =  tempY - 80;
                                             if( verticale > ( htmlheight - 180 )) $('rec_dett').style.top = htmlheight - 200 + "px";
                                             else $('rec_dett').style.top = tempY - 80 + "px";
                                             $('rec_dett').style.display= "inline";
                                            }.bind(this);
                                this.dett.onmouseout =  function() { $('rec_dett').innerHTML='';
                                                                     $('rec_dett').style.display= "none"; };
                                Element.addClassName(this.dett, 'dett');


				this.dl = document.createElement('a');
				this.dl.href = "javascript:go()";
				this.dl.title = "Inoltra via e-mail";
				this.dl.innerHTML = "<img src=\"images/email.png\">";
				Element.addClassName(this.dl, 'inoltra');

				this.del = document.createElement('a');
				this.del.href = "javascript:go()"
				this.del.title = "Elimina fax";
				this.del.innerHTML = "<img src=\"images/trash.png\">";
				Element.addClassName(this.del, 'elimina');

				this.span.onmousedown    = this.select.bind(this);
				this.link.onmousedown    = this.select.bindAsEventListener(this);
				this.ante.onmousedown    = this.select.bindAsEventListener(this);
				this.readfax.onmousedown = this.select.bindAsEventListener(this);
				this.dett.onmousedown    = this.select.bindAsEventListener(this);
				this.icon.onmousedown    = this.select.bind(this);
				this.inoltro.onmousedown = this.select.bind(this);
				this.img_fax.onmousedown = this.select.bind(this);
				this.abook.onmousedown   = this.addBook.bind(this);
				this.link.ondblclick     = this.faxView.bindAsEventListener(this);
				this.readfax.onclick     = this.addRead.bind(this);
				this.ante.ondblclick     = this.faxView.bindAsEventListener(this);
				this.dett.ondblclick     = this.faxView.bindAsEventListener(this);
				this.icon.ondblclick     = this.faxView.bindAsEventListener(this);
				this.inoltro.ondblclick  = this.faxView.bindAsEventListener(this);
				this.img_fax.ondblclick  = this.faxView.bindAsEventListener(this);
				this.abook.onclick       = this.addBook.bind(this);
				this.dl.onclick          = this.addDl.bind(this);
				this.del.onclick         = this.unlink.bind(this);

				this.handle.appendChild(this.icon);
				this.handle.appendChild(this.img_fax);
				this.handle.appendChild(this.abook);
				this.handle.appendChild(this.link);
				this.handle.appendChild(this.ante);
				this.handle.appendChild(this.readfax);
				this.handle.appendChild(this.dett);
				this.handle.appendChild(this.inoltro);
				this.span.appendChild(this.handle);
				this.span.appendChild(this.faxdate);
				this.span.appendChild(this.pagine);
				if(!this.readonly) {
						this.span.appendChild(this.del);
				}
				this.span.appendChild(this.dl);
				this.element.appendChild(this.span);

				} else if ( this.fax_type =='I') {

				this.element = document.createElement('div');
				this.span =    document.createElement('span');
				this.link =    document.createElement('a');
				this.ante =    document.createElement('a');
				this.invio=    document.createElement('a');
				this.tenta=    document.createElement('a');
				this.tenta2=   document.createElement('a');
				this.icon =    document.createElement('img');
				this.img_fax = document.createElement('img');
				this.abook  =  document.createElement('img');
				this.handle =  document.createElement('div');
				Element.addClassName(this.handle, 'handle');

				this.flag != 'normal' ? this.icon.src = "images/"+this.flag+".png" : this.icon.src= fileIcon;
				Element.addClassName(this.icon, 'icon3');
	
				percorso = this.rpath.replace(/sentm/, "Invii Multipli");
				percorso = percorso.replace(/sent/, "Inviati");

                                if (!this.sender && !this.number) { this.link.innerHTML="Sconosciuto";
                                                                    if(FC.SELECTEDMODE=='advsearch') this.link.title = percorso;
                                } else  if (this.sender!= '')  {   this.link.innerHTML = this.sender.substring(0,20);
                                                                   if(FC.SELECTEDMODE!='advsearch') this.link.title = this.number;
                                                                   else this.link.title = percorso; 
                                } else { this.link.innerHTML = this.number;
                                       if(FC.SELECTEDMODE=='advsearch') this.link.title = percorso }
				Element.addClassName(this.link, 'link2');

				this.abook.title = "Aggiungi in Rubrica FaxWeb";
				this.abook.src = "images/freccia_avanti.gif" ;
				Element.addClassName(this.abook, 'abook');

				this.faxdate = document.createElement('span');
				this.faxdate.innerHTML = this.fax_date;
				Element.addClassName(this.faxdate, 'date');


                                if(this.description=='') { this.ante.innerHTML = "<img src='images/view.png'>";
                                                           this.ante.onmouseover =
                                                           function() {
                                                                        $('preview').innerHTML="<span>" + percorso + "<br><br><img src='faxAnt.php?id="+this.rpath+'/'+ this.fax_filename + "-15.png' height=280 width=600> </span>";
                                                                        $('preview').style.display= "inline"; }.bind(this);
                                                           this.ante.onmouseout =  function() { $('preview').innerHTML='';
                                                                                                $('preview').style.display= "none"; };
                                                           Element.addClassName(this.ante, 'ante');
                                } else {
                                        this.ante.innerHTML = "<img src='images/view.png'>";
                                        this.ante.onmouseover = 
                                        function() {
                                                    $('preview').innerHTML="<span>" + percorso + "<br>" +  this.description +"<br><img src='faxAnt.php?id="+this.rpath+'/'+ this.fax_filename +"-15.png' height=280 width=600> </span>";
                                                    $('preview').style.display= "inline"; }.bind(this);
                                        this.ante.onmouseout =  function() { $('preview').innerHTML='';
                                                                             $('preview').style.display= "none"; };
                                        Element.addClassName(this.ante, 'ante');
                                }      

				this.pagine = document.createElement('span');
				this.pagine.innerHTML = this.pages;
				Element.addClassName(this.pagine, 'pagine');

				this.invio.innerHTML = this.user;
				Element.addClassName(this.invio, 'inoltro');

				if (this.state == '') {   this.img_fax.src   = "images/clock.png";  
							  this.img_fax.title = "Invio in Corso";
				} else if (this.state < '7' )     { this.img_fax.src   = "images/important.png" ;
			        if (this.state =='1' ) this.img_fax.title = "Sospeso";
				if (this.state =='2' ) this.img_fax.title = "In Attesa di invio all'orario stabilito";
  				if (this.state =='3' ) this.img_fax.title = "Problemi di connessione,in attesa di ritrasmissione";
			        if (this.state =='4' ) this.img_fax.title = "Numero Occupato";
  				if (this.state =='5' ) this.img_fax.title = "Pronto ad essere inviato";
			        if (this.state =='6' ) this.img_fax.title = "Invio in corso...";
				} else if (this.state == '7' ) { this.img_fax.src   = "images/ledgreen.png";
								 this.img_fax.title = "Inviato Correttamente";
				} else if (this.state == '8' ) { this.img_fax.src   = "images/ledred.png";
								 this.img_fax.title = "Non Inviato";
				} else if (this.state == '99') { this.img_fax.src   = "images/ledyellow.png";
								 this.img_fax.title = "Interrotto dall'utente"; }
				Element.addClassName(this.img_fax, 'img_fax2');

                                if ( this.state=='7'){
                                                       this.tenta.innerHTML = this.attempts;
                                                       this.tenta.onmouseover =
                                                       function(e) {
                                                                    var IE = document.all?true:false
                                                                    var tempX = 0
                                                                    var tempY = 0
                                                                    if (IE) {
                                                                             tempX = event.clientX + document.body.scrollLeft
                                                                             tempY = event.clientY + document.body.scrollTop
                                                                    } else {
                                                                             tempX = e.pageX
                                                                             tempY = e.pageY
                                                                    }
                                                                    if (tempX < 0){tempX = 0}
                                                                    if (tempY < 0){tempY = 0}

                                                                    $('send_dett').innerHTML="<span>Stato : " + this.img_fax.title + " (" + this.state + ")<br>Esito : OK<br>Tentativi : " + this.attempts + "</span>";                
                                                                    $('send_dett').style.left= tempX - 220 + "px";
                                                                    var verticale = tempY - 50;
                                                                    if( verticale > ( htmlheight - 100 )) $('send_dett').style.top = htmlheight - 100 + "px";
                                                                    else $('send_dett').style.top= tempY - 50 + "px";
                                                                    $('send_dett').style.display= "inline"; }.bind(this);

                                                       this.tenta.onmouseout =  function() { $('send_dett').innerHTML='';
                                                                                             $('send_dett').style.display= "none"; };
				} else if ( this.state!= '7' && this.state!= '8' && this.attempts!='0') { 
                                                       this.tenta.innerHTML = this.attempts;
                                                       this.tenta.onmouseover =
                                                       function(e) {
                                                                    var IE = document.all?true:false
                                                                    var tempX = 0
                                                                    var tempY = 0
                                                                    if (IE) {
                                                                             tempX = event.clientX + document.body.scrollLeft
                                                                             tempY = event.clientY + document.body.scrollTop
                                                                    } else {
                                                                             tempX = e.pageX
                                                                             tempY = e.pageY
                                                                    }
                                                                    if (tempX < 0){tempX = 0}
                                                                    if (tempY < 0){tempY = 0}

                                                                    $('send_dett').innerHTML="<span>Stato : " + this.img_fax.title + " (" + this.state + ")<br>Esito : " + this.esito + "<br>Tentativi : " + this.attempts + "<br>Prossimo Tentativo : " + this.tts + "</span>";   
                                                                    $('send_dett').style.left= tempX - 220 + "px";
                                                                    var verticale = tempY - 50;
                                                                    if( verticale > ( htmlheight - 100 )) $('send_dett').style.top = htmlheight - 100 + "px";
                                                                    else $('send_dett').style.top= tempY - 50 + "px";
                                                                    $('send_dett').style.display= "inline"; }.bind(this);

                                                       this.tenta.onmouseout =  function() { $('send_dett').innerHTML='';
                                                                                             $('send_dett').style.display= "none"; };
				} else { 
                                                       this.tenta.innerHTML = this.attempts;
                                                       this.tenta.onmouseover =
                                                       function(e) {
                                                                    var IE = document.all?true:false
                                                                    var tempX = 0
                                                                    var tempY = 0
                                                                    if (IE) {
                                                                             tempX = event.clientX + document.body.scrollLeft
                                                                             tempY = event.clientY + document.body.scrollTop
                                                                    } else {
                                                                             tempX = e.pageX
                                                                             tempY = e.pageY
                                                                    }
                                                                    if (tempX < 0){tempX = 0}
                                                                    if (tempY < 0){tempY = 0}

                                                                    $('send_dett').innerHTML="<span>Stato : " + this.img_fax.title + " (" + this.state + ")<br>Esito : " + this.esito + "<br>Tentativi : " + this.attempts + "</span>";
                                                                    $('send_dett').style.left= tempX - 220 + "px";
                                                                    var verticale = tempY - 50;
                                                                    if( verticale > ( htmlheight - 100 )) $('send_dett').style.top = htmlheight - 100 + "px";
                                                                    else $('send_dett').style.top= tempY - 50 + "px";
                                                                    $('send_dett').style.display= "inline"; }.bind(this);

                                                       this.tenta.onmouseout =  function() { $('send_dett').innerHTML='';
                                                                                             $('send_dett').style.display= "none"; };
                                }
				Element.addClassName(this.tenta, 'tenta');

                                if( this.resends == '0') {
                                                        this.tenta2.innerHTML = "";
                                } else {
                                        this.tenta2.innerHTML = "<img src=\"images/resend_rcp.png\">";
                                        this.tenta2.onmouseover =
                                        function(e) {
                                                     var IE = document.all?true:false
                                                     var tempX = 0
                                                     var tempY = 0
                                                     if (IE) {
                                                              tempX = event.clientX + document.body.scrollLeft
                                                              tempY = event.clientY + document.body.scrollTop
                                                     } else {
                                                              tempX = e.pageX
                                                              tempY = e.pageY
                                                     }
                                                     if (tempX < 0){tempX = 0}
                                                     if (tempY < 0){tempY = 0}

                                                    $('resend').innerHTML="<span>"+  this.resend_rcp + "</span>";
                                                    $('resend').style.left= tempX - 230 + "px";
                                                    var verticale = tempY - 50;
                                                    if( verticale > ( htmlheight - 100 )) $('resend').style.top = htmlheight - 100 + "px";
                                                    else $('resend').style.top= tempY - 50 + "px";
                                                    $('resend').style.display= "inline"; }.bind(this);
                                        this.tenta2.onmouseout =  function() { $('resend').innerHTML='';
                                                                               $('resend').style.display= "none"; };
                                }
				Element.addClassName(this.tenta2, 'tenta2');

				this.resend = document.createElement('a');
				this.resend.href = "javascript:go()";
				this.resend.title = "Invia di nuovo";
				this.resend.innerHTML = "<img src=\"images/resend.png\">";
				Element.addClassName(this.resend, 'resend');

				this.del = document.createElement('a');
				this.del.href = "javascript:go()";
				if( this.state=='7' || this.state=='8' || this.state=='99') {
												this.del.title = "Elimina fax";
												this.del.innerHTML = "<img src=\"images/trash.png\">";
				} else if ( this.tipo=='W' || this.tipo=='H') { 
										this.del.title = "Interrompi Invio Fax";
										this.del.innerHTML = "<img src=\"images/stop.png\">";
				}
				Element.addClassName(this.del, 'elimina');
				this.del.onclick         = this.unlink.bind(this);
				if(!this.readonly) { this.span.appendChild(this.del); }

				this.invio.onmousedown   = this.select.bind(this);
				this.span.onmousedown    = this.select.bind(this);
				this.link.onmousedown    = this.select.bindAsEventListener(this);
				this.ante.onmousedown    = this.select.bindAsEventListener(this);
				this.tenta.onmousedown   = this.select.bindAsEventListener(this);
				this.tenta2.onmousedown  = this.select.bind(this);
				this.icon.onmousedown    = this.select.bind(this);
				this.img_fax.onmousedown = this.select.bind(this);
				this.abook.onmousedown   = this.addBook.bind(this);
				this.link.ondblclick     = this.faxView.bindAsEventListener(this);
				this.invio.ondblclick    = this.faxView.bindAsEventListener(this);
				this.ante.ondblclick     = this.faxView.bindAsEventListener(this);
				this.tenta.ondblclick    = this.faxView.bindAsEventListener(this);
				this.tenta2.ondblclick   = this.faxView.bindAsEventListener(this);
				this.icon.ondblclick     = this.faxView.bindAsEventListener(this);
				this.img_fax.ondblclick  = this.faxView.bindAsEventListener(this);
				this.abook.onclick       = this.addBook.bind(this);
				this.resend.onclick      = this.addResend.bind(this);

				this.handle.appendChild(this.icon);
				this.handle.appendChild(this.img_fax);
				this.handle.appendChild(this.abook);
				this.handle.appendChild(this.link);
				this.handle.appendChild(this.ante);
				this.handle.appendChild(this.tenta);
				this.handle.appendChild(this.tenta2);
				this.handle.appendChild(this.invio);
				this.span.appendChild(this.handle);
				this.span.appendChild(this.faxdate);
				this.span.appendChild(this.pagine);
				this.span.appendChild(this.resend);
				this.element.appendChild(this.span);

				}
				this.search ? this.element.id = 'sid'+this.id : this.element.id = 'fid'+this.id;
				this.element.object = this;
				Element.addClassName(this.element, 'file');
				this.parentElement.insertBefore(this.element, this.parentElement.firstChild);
				!this.readonly ? new Draggable(this.element.id, {revert:true, handle:'handle'}) : null;
},

appearTools: function () { Effect.Appear(this.del.id); },
fadeTools:   function () { this.del.style.display="none";  },

addRead: function() {
	var params = $H({ faxweb: 'addRead', id: this.id , letto: this.letto});
	var ajax = new Ajax.Request(FC.URL, {
	onSuccess: this.addRead_handler.bind(this),
	method: 'post',
	parameters: params.toQueryString(),
	onFailure: function() { showError(ER.ajax); } }); 
},

addRead_handler: function (response) {
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
	if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
},

addDl: function() {  $('InoltraList').style.display= "inline"; },

addBook: function() {
	$('Address_book').style.display= "inline"; 
	$('fax_number').value = this.number;   
	$('fax_address').value = this.sender;   
},

addResend: function() {  
	if(confirm('Inviare di nuovo il Fax?')) {
			var params = $H({ faxweb: 'ResendFax', id: this.id , type: "single"});
			var ajax = new Ajax.Request(FC.URL, {
								onSuccess: this.addResend_handler.bind(this),
								method: 'post',
								parameters: params.toQueryString(),
								onFailure: function() { showError(ER.ajax); } }); }
},

addResend_handler: function (response) {
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
	if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update(); 
},

download: function () { location.href = FC.URL + '?faxweb=getFile&fileid=' + this.id; },

faxView:  function () { 
	var params = $H({ faxweb: 'FaxLetto', id: this.id });
	var ajax = new Ajax.Request(FC.URL, {
						onSuccess: this.addLetto_handler.bind(this),
						method: 'post',
						parameters: params.toQueryString(),
						onFailure: function() { showError(ER.ajax); } }); 
},
addLetto_handler: function (response) {
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
	else { if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
      	//window.open("faxView.php?id=" + this.id, "", "width=600, height=800, scrollbar, resizable, status"); }
      	location.href = "faxView.php?id=" + this.id; }
},

refresh: function () {  
        var htmlheight = document.body.parentNode.scrollHeight;
	if ( this.fax_type =='R') {

  		  Element.removeClassName(this.icon, 'icon2');
		  this.flag != 'normal' ? this.icon.src = "images/"+this.flag+".png" : this.icon.src= fileIcon;
		  Element.addClassName(this.icon, 'icon2');

		  Element.removeClassName(this.readfax, 'read');
		  Element.removeClassName(this.readfax, 'read2');
		  if (this.letto=='0') { this.readfax.innerHTML = "<img src='images/nonletto.png'>";
					 this.readfax.title = "";
					 Element.addClassName(this.readfax, 'read');
		  } else { this.readfax.innerHTML = "<img src='images/letto.png'>";
			   this.readfax.title = this.letto;
			   Element.addClassName(this.readfax, 'read2'); }

		  Element.removeClassName(this.link, 'link2');
		  Element.removeClassName(this.link, 'link3');
                  if (!this.sender && !this.number) { this.link.innerHTML="Sconosciuto";
                                                      if(FC.SELECTEDMODE=='advsearch') this.link.title = this.rpath.replace(/received/, "Ricevuti");
                  } else  if (this.sender!= '')  { this.link.innerHTML = this.sender.substring(0,20);
                                                   if(FC.SELECTEDMODE!='advsearch') this.link.title = this.number;
                                                   else this.link.title = this.rpath.replace(/received/, "Ricevuti"); 
                  } else { this.link.innerHTML = this.number;
                                       if(FC.SELECTEDMODE=='advsearch') this.link.title = this.rpath.replace(/received/, "Ricevuti"); }
		  if (this.letto=='0')  Element.addClassName(this.link, 'link3');
		  else  Element.addClassName(this.link, 'link2'); 

		  Element.removeClassName(this.faxdate, 'date2');
		  Element.removeClassName(this.faxdate, 'date');
		  this.faxdate.innerHTML = this.date;
		  if (this.letto=='0') Element.addClassName(this.faxdate, 'date2');
		  else  Element.addClassName(this.faxdate, 'date');

		  Element.removeClassName(this.pagine, 'pagine2');
		  Element.removeClassName(this.pagine, 'pagine');
		  this.pagine.innerHTML = this.pages;
		  if (this.letto=='0') Element.addClassName(this.pagine, 'pagine2');
		  else  Element.addClassName(this.pagine, 'pagine');

                  if( this.forward_rcp == '') {
                                                this.inoltro.innerHTML = "";
                  } else {
                           this.inoltro.innerHTML = this.forward_rcp.split("(",1) + "...";
                           this.inoltro.onmouseover =
                           function(e) {
                                       var IE = document.all?true:false
                                       var tempX = 0
                                       var tempY = 0
                                       if (IE) {
                                                tempX = event.clientX + document.body.scrollLeft
                                                tempY = event.clientY + document.body.scrollTop
                                       } else {
                                                tempX = e.pageX
                                                tempY = e.pageY
                                       }
                                       if (tempX < 0){tempX = 0}
                                       if (tempY < 0){tempY = 0}
                                       $('inoltri').innerHTML="<span>"+  this.forward_rcp + "</span>";
                                       $('inoltri').style.left= tempX - 380 + "px";
                                       var verticale = tempY - 30;
                                       if( verticale > ( htmlheight - 65 )) $('inoltri').style.top = htmlheight - 65 + "px";
                                       else $('inoltri').style.top = tempY - 30 + "px";
                                       $('inoltri').style.display= "inline";
                                      }.bind(this);
                           this.inoltro.onmouseout =  function() { $('inoltri').innerHTML='';
                                                                   $('inoltri').style.display= "none"; };
                  }
		  Element.removeClassName(this.inoltro, 'inoltro2');
		  Element.removeClassName(this.inoltro, 'inoltro');
		  if (this.letto=='0') Element.addClassName(this.inoltro, 'inoltro2');
		  else Element.addClassName(this.inoltro, 'inoltro');

		  this.link.onmousedown    = this.select.bindAsEventListener(this);
		  this.inoltro.onmousedown = this.select.bind(this);
		  this.link.ondblclick     = this.faxView.bindAsEventListener(this);
		  this.inoltro.ondblclick  = this.faxView.bindAsEventListener(this);
		  this.handle.appendChild(this.link);
		  this.handle.appendChild(this.inoltro);

	} else if ( this.fax_type =='I') {

		  Element.removeClassName(this.icon, 'icon3');
		  this.flag != 'normal' ? this.icon.src = "images/"+this.flag+".png" : this.icon.src= fileIcon;
		  Element.addClassName(this.icon, 'icon3');
	
		  Element.removeClassName(this.link, 'link2');
                  if (!this.sender && !this.number) { this.link.innerHTML="Sconosciuto";
                                                      if(FC.SELECTEDMODE=='advsearch') this.link.title = percorso;
                  } else  if (this.sender!= '')  {   this.link.innerHTML = this.sender.substring(0,20);
                                                     if(FC.SELECTEDMODE!='advsearch') this.link.title = this.number;
                                                     else this.link.title = percorso;
                  } else { this.link.innerHTML = this.number;
                           if(FC.SELECTEDMODE=='advsearch') this.link.title = percorso }
		  Element.addClassName(this.link, 'link2');

		  Element.removeClassName(this.faxdate, 'date');
		  this.faxdate.innerHTML = this.date;
		  Element.addClassName(this.faxdate, 'date');

		  argomento = this.name.split(".");
		  this.fax_filename = argomento[0];
		  percorso = this.rpath.replace(/sentm/, "Invii Multipli");
		  percorso = percorso.replace(/sent/, "Inviati");

                  if(this.description=='') { 
					    Element.removeClassName(this.ante, 'ante');
                                            this.ante.innerHTML = "<img src='images/view.png'>";
                                            this.ante.onmouseover =
                                            function() {
                                                        $('preview').innerHTML="<span>" + percorso + "<br><br><img src='faxAnt.php?id="+this.rpath+'/'+ this.fax_filename + "-15.png' height=280 width=600> </span>";
                                                        $('preview').style.display= "inline"; }.bind(this);
                                            this.ante.onmouseout =  function() { $('preview').innerHTML='';
                                                                                 $('preview').style.display= "none"; };
                                            Element.addClassName(this.ante, 'ante');
                  } else {
			  Element.removeClassName(this.ante, 'ante');
                          this.ante.innerHTML = "<img src='images/view.png'>";
                          this.ante.onmouseover =
                          function() {
                                      $('preview').innerHTML="<span>" + percorso + "<br>" +  this.description +"<br><img src='faxAnt.php?id="+this.rpath+'/'+ this.fax_filename +"-15.png' height=280 width=600> </span>";
                                      $('preview').style.display= "inline"; }.bind(this);
                          this.ante.onmouseout =  function() { $('preview').innerHTML='';
                                                               $('preview').style.display= "none"; };
                          Element.addClassName(this.ante, 'ante');
                  }

		  Element.removeClassName(this.img_fax, 'img_fax2');
		  if (this.state == '') {   this.img_fax.src   = "images/clock.png";
					    this.img_fax.title = "Invio in Corso";
		  } else if (this.state < '7' )     { this.img_fax.src   = "images/important.png" ;
	          if (this.state =='1' ) this.img_fax.title = "Sospeso";
		  if (this.state =='2' ) this.img_fax.title = "In Attesa di invio all'orario stabilito";
		  if (this.state =='3' ) this.img_fax.title = "Problemi di connessione,in attesa di ritrasmissione";
                  if (this.state =='4' ) this.img_fax.title = "Numero Occupato";
                  if (this.state =='5' ) this.img_fax.title = "Pronto ad essere inviato";
                  if (this.state =='6' ) this.img_fax.title = "Invio in corso...";
		  } else if (this.state == '7' ) { this.img_fax.src   = "images/ledgreen.png";
						   this.img_fax.title = "Inviato Correttamente";
		  } else if (this.state == '8' ) { this.img_fax.src   = "images/ledred.png";
						   this.img_fax.title = "Non Inviato";
		  } else if (this.state == '99') { this.img_fax.src   = "images/ledyellow.png";
						   this.img_fax.title = "Interrotto dall'utente"; }
		  Element.addClassName(this.img_fax, 'img_fax2');

		  Element.removeClassName(this.tenta, 'tenta');
                  if ( this.state=='7'){
                                        this.tenta.innerHTML = this.attempts;
                                        this.tenta.onmouseover =
                                        function(e) {
                                                      var IE = document.all?true:false
                                                      var tempX = 0
                                                      var tempY = 0
                                                      if (IE) {
                                                               tempX = event.clientX + document.body.scrollLeft
                                                               tempY = event.clientY + document.body.scrollTop
                                                      } else {
                                                               tempX = e.pageX
                                                               tempY = e.pageY
                                                      }
                                                      if (tempX < 0){tempX = 0}
                                                      if (tempY < 0){tempY = 0}
                                                      $('send_dett').innerHTML="<span>Stato : " + this.img_fax.title + " (" + this.state + ")<br>Esito : OK<br>Tentativi : " + this.attempts + "</span>";
                                                      $('send_dett').style.left= tempX - 220 + "px";
                                                      var verticale = tempY - 50;
                                                      if( verticale > ( htmlheight - 100 )) $('send_dett').style.top = htmlheight - 100 + "px";
                                                      else $('send_dett').style.top= tempY - 50 + "px";
                                                      $('send_dett').style.display= "inline"; }.bind(this);
                                                      this.tenta.onmouseout =  function() { $('send_dett').innerHTML='';
                                                                                            $('send_dett').style.display= "none"; };

                  } else if ( this.state!= '7' && this.state!= '8' && this.attempts!='0') {
                                                      this.tenta.innerHTML = this.attempts;
                                                      this.tenta.onmouseover =
                                                      function(e) {
                                                                   var IE = document.all?true:false
                                                                   var tempX = 0
                                                                   var tempY = 0
                                                                   if (IE) {
                                                                            tempX = event.clientX + document.body.scrollLeft
                                                                            tempY = event.clientY + document.body.scrollTop
                                                                   } else {
                                                                            tempX = e.pageX
                                                                            tempY = e.pageY
                                                                   }
                                                                   if (tempX < 0){tempX = 0}
                                                                   if (tempY < 0){tempY = 0}
                                                                   $('send_dett').innerHTML="<span>Stato : " + this.img_fax.title + " (" + this.state + ")<br>Esito : " + this.esito + "<br>Tentativi : " + this.attempts + "<br>Prossimo Tentativo : " + this.tts + "</span>";
                                                                   $('send_dett').style.left= tempX - 220 + "px";
                                                                   var verticale = tempY - 50;
                                                                   if( verticale > ( htmlheight - 100 )) $('send_dett').style.top = htmlheight - 100 + "px";
                                                                   else $('send_dett').style.top= tempY - 50 + "px";
                                                                   $('send_dett').style.display= "inline"; }.bind(this);
                                                      this.tenta.onmouseout =  function() { $('send_dett').innerHTML='';
                                                                                            $('send_dett').style.display= "none"; };
                  } else {
                                                      this.tenta.innerHTML = this.attempts;
                                                      this.tenta.onmouseover =
                                                      function(e) {
                                                                   var IE = document.all?true:false
                                                                   var tempX = 0
                                                                   var tempY = 0
                                                                   if (IE) {
                                                                            tempX = event.clientX + document.body.scrollLeft
                                                                            tempY = event.clientY + document.body.scrollTop
                                                                   } else {
                                                                            tempX = e.pageX
                                                                            tempY = e.pageY
                                                                   }
                                                                   if (tempX < 0){tempX = 0}
                                                                   if (tempY < 0){tempY = 0}
                                                                   $('send_dett').innerHTML="<span>Stato : " + this.img_fax.title + " (" + this.state + ")<br>Esito : " + this.esito + "<br>Tentativi : " + this.attempts + "</span>";
                                                                   $('send_dett').style.left= tempX - 220 + "px";
                                                                   var verticale = tempY - 50;
                                                                   if( verticale > ( htmlheight - 100 )) $('send_dett').style.top = htmlheight - 100 + "px";
                                                                   else $('send_dett').style.top= tempY - 50 + "px";
                                                                   $('send_dett').style.display= "inline"; }.bind(this);

                                                      this.tenta.onmouseout =  function() { $('send_dett').innerHTML='';
                                                                                            $('send_dett').style.display= "none"; };

                  }
                  Element.addClassName(this.tenta, 'tenta');

		  Element.removeClassName(this.tenta2, 'tenta2');
                  if( this.resends == '0') {
                                            this.tenta2.innerHTML = "";
                  } else {
                          this.tenta2.innerHTML = "<img src=\"images/resend_rcp.png\">";
                          this.tenta2.onmouseover =
                          function(e) {
                                        var IE = document.all?true:false
                                        var tempX = 0
                                        var tempY = 0
                                        if (IE) {
                                                 tempX = event.clientX + document.body.scrollLeft
                                                 tempY = event.clientY + document.body.scrollTop
                                        } else {
                                                tempX = e.pageX
                                                tempY = e.pageY
                                        }
                                        if (tempX < 0){tempX = 0}
                                        if (tempY < 0){tempY = 0}

                                        $('resend').innerHTML="<span>"+  this.resend_rcp + "</span>";
                                        $('resend').style.left= tempX - 230 + "px";
                                        var verticale = tempY - 50;
                                        if( verticale > ( htmlheight - 100 )) $('resend').style.top = htmlheight - 100 + "px";
                                        else $('resend').style.top= tempY - 50 + "px";
                                        $('resend').style.display= "inline"; }.bind(this);
                                        this.tenta2.onmouseout =  function() { $('resend').innerHTML='';
                                                                               $('resend').style.display= "none"; };
                                }
                                Element.addClassName(this.tenta2, 'tenta2');


		  Element.removeClassName(this.del, 'elimina');
		  this.del.href = "javascript:go()";
		  if( this.state=='7' || this.state=='8' || this.state=='99') {
 										  this.del.title = "Elimina fax";
										  this.del.innerHTML = "<img src=\"images/trash.png\">";
		  } else if ( this.tipo=='W' || this.tipo=='H') {
								  this.del.title = "Interrompi Invio Fax";
								  this.del.innerHTML = "<img src=\"images/stop.png\">";
		  }
		  Element.addClassName(this.del, 'elimina');

		  this.del.onclick         = this.unlink.bind(this);
		  if(!this.readonly) { this.span.appendChild(this.del); }
		  this.tenta.onmousedown   = this.select.bindAsEventListener(this);
		  this.img_fax.onmousedown = this.select.bind(this);
		  this.tenta.ondblclick    = this.faxView.bindAsEventListener(this);
		  this.img_fax.ondblclick  = this.faxView.bindAsEventListener(this);
		  this.handle.appendChild(this.img_fax);
		  this.handle.appendChild(this.tenta);
	}
},

select: function (ev) {
	$('uploadPath').value = this.parentObject.path;
	$('uploadstatus').innerHTML = "<em>Destinazione</em> "+this.parentObject.path;

	if(FC.SELECTEDOBJECT != null && FC.SELECTEDOBJECT != this && FC.SELECTEDOBJECT.type==this.type) FC.SELECTEDOBJECT.deselect(); 
//	window.onkeypress = this.select_handler.bindAsEventListener(this);
	FC.SELECTEDOBJECT = this;
	Element.addClassName(this.span, 'selected');

	if ($('meta').prevElement != this.id) { this.getMeta(); }
	return false;
},

select_handler: function (event) {
	var charCode = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode); 
	if (charCode == Event.KEY_DOWN) this.parentObject.nextChild(this); 
	else if (charCode == Event.KEY_UP) this.parentObject.prevChild(this); 
	else if (charCode == Event.KEY_LEFT) this.parentObject.select(this);				
},

deselect: function () {
	this.timer = null;
	window.onkeypress = null;
	Element.removeClassName(this.span, 'selected');
	this.selected = false; 
	this.clearRename();
},

getMeta: function () {
	$('meta').prevElement = this.id;

	var params = $H({ faxweb: 'getMeta', fileid: this.id });
	var ajax = new Ajax.Request(FC.URL,{
						onLoading: showMetaSpinner(), 
						onSuccess: this.getMeta_handler.bind(this),
						method: 'post', 
						parameters: params.toQueryString(), 
						onFailure: function() { showError(ER.ajax); }
					   });
},

getMeta_handler: function (response) {
	var json_data = response.responseText;		
	eval("var jsonObject = ("+json_data+")");
	var meta = {	        edit		: jsonObject.bindings[0].edit,
				filename  : jsonObject.bindings[1].filename,
				date		: jsonObject.bindings[1].date,
				downloads : jsonObject.bindings[1].downloads,
				flag		: jsonObject.bindings[1].flags,
				type		: jsonObject.bindings[1].type || 'Document',
				description: jsonObject.bindings[1].description,
				size		: jsonObject.bindings[1].size,
				file		: true,
				id			: this.id,
				image		: jsonObject.bindings[1].image,
				path		: jsonObject.bindings[1].path
			};
	updateMeta(meta);
},

clear: function () {
	for (var i=0;i<this.parentObject.children.length;i++){
		if (this.parentObject.children[i] == this) {
			this.parentObject.children.splice(i,1);
		break;
		}
	}
	Element.remove(this.element);
},

unlink: function () {
	if(this.fax_type=='I' && this.state=='7' || this.state=='8' || this.state=='99' || this.fax_type=='R' ) {
		if(confirm('Eliminare il Fax?')) {
						if(this.readonly) return false;
						var params = $H({ faxweb: 'fileDelete', fileid: this.id });
						var ajax = new Ajax.Request(FC.URL,{
											onSuccess: this.clear.bind(this),
											method: 'post',
											parameters: params.toQueryString(),
											onFailure: function() { showError(ER.ajax); }
										});} 
	} else if ( this.fax_type=='I' && this.tipo=='W' || this.tipo=='H') {
		if(confirm('Fermare l\'invio del Fax?')) {
						var params = $H({ faxweb: 'stopSend', job_id: this.job_id });
						var ajax = new Ajax.Request(FC.URL,{
											onSuccess: this.stopSend.bind(this),
											method: 'post',
											parameters: params.toQueryString(),
											onFailure: function() { showError(ER.ajax); }
										});} 
		}
},

stopSend: function (response) {
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
	if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
},

clearRename: function () {
	if(this.renameIsOpen) {
				this.newName.style.display="none";
				Element.remove(this.newName);
				this.link.style.display = "block";
				this.renameIsOpen = false;		
				this.getMeta();
	}
},

rename_handler: function (event) {
	var charCode = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode); 
	if (charCode == Event.KEY_ESC) this.clearRename();
	if (charCode == Event.KEY_RETURN) {
			var params = $H({
						faxweb : 'fileRename',
						fileid : this.id,
						filename: this.newName.value
					});
	this.link.innerHTML = this.newName.value;
	this.name = this.newName.value;
			var ajax = new Ajax.Request(FC.URL, {
						onComplete: this.clearRename.bind(this),
						onSuccess: this.refresh.bind(this),
						method: 'post', 
						parameters: params.toQueryString(), 
						onFailure: function() { showError(ER.ajax); }
					});
	}		

},

showRename: function () {
	if(this.readonly) return false;
	this.renameIsOpen = true;
	this.newName = document.createElement('input');
	this.newName.type = 'text';
	this.newName.size = '40';
	this.newName.name = this.id;
	this.newName.value = this.name;
	window.onkeypress = this.rename_handler.bindAsEventListener(this);
	Element.addClassName(this.newName, 'renamefield');
	this.link.style.display = "none";
	this.span.appendChild(this.newName);
	this.newName.focus();
	this.newName.select();
},

update: function () {
	this.parentObject.update();
}
};

var ForwardEntry = Class.create();
ForwardEntry.prototype = {

initialize: function (id, parent, name, email) {
	this.id= id;
	this.name= name;
	this.email= email;
	this.element = document.createElement('option');
	this.element.value= email;
	this.element.innerHTML = name;
}

};

var ForwardList = Class.create();
ForwardList.prototype = {

initialize: function (element) {
	this.element = element;
	this.children = Array();
	if(document.forms['forward'].CambiaMostra.value) {
			this.getContents(document.forms['forward'].CambiaMostra.value);
	} else {
			this.getContents('all'); }        
},

getContents: function (t) {
	if(this.opening) return false;
	this.opening = true;
	var params = $H({ faxweb: 'getForward', type: t });
	var ajax = new Ajax.Request(FC.URL, {
						onSuccess: this.getContents_handler.bind(this),
						method: 'post',
						parameters: params.toQueryString(),
						onFailure: function() { showError(ER.ajax); }
					    });
},

getContents_handler: function (response) {
	this.opening = false;
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings.length == 0) { this.addBlank(); return true; }
	if(jsonObject.bindings[0].error)  alert(jsonObject.bindings[0].error);

	for(var	 i=0; i < jsonObject.bindings.length; i++) { this.addEntry(jsonObject.bindings[i].name, jsonObject.bindings[i].address); }
	this.parentElement.appendChild(this.element);
},

addEntry: function (name, email) {
	this.id = this.children.length; 
	var e = new ForwardEntry(this.id, this.element, name, email )
	this.children.push(e);
	this.element.appendChild(e.element);
},

removeAllEntry: function () {
	for (var i = 0; i < this.children.length; i++) { this.element.removeChild(this.children[i].element); }
	this.children = Array();
}
};

// NON OBJECT METHODS

updateMeta = function (meta) {
	meta = $H(meta);
	$('meta').innerHTML = '';
	var path = meta.path.replace('/', ' /');
	if(meta.file) {
		var normalflag = hotflag = emergencyflag = '';
		
		switch(meta.flag) {
			case 'normal': normalflag = 'selected'; break;
			case 'hot': hotflag = 'selected'; break;
			case 'emergency': emergencyflag = 'selected'; break;
		}
		
		var metaFlags = '<option label="Normal" value="normal" '+normalflag+' >Normal</option><option label="Hot" value="hot" '+hotflag+' >Hot</option><option label="Emergency" value="emergency" '+emergencyflag+'>Emergency</option>';
		if(meta.image == '1') $('meta').innerHTML += '<div class="thumbbox"><a href="'+FC.URL+'?faxweb=getFile&fileid='+meta.id+'" ><img src="'+FC.URL+'?faxweb=getThumb&fileid='+meta.id+'" class="metaThumbnail" alt="" width="220" height="190" /></a></div>';
		$('meta').innerHTML += ' <table><tr><td class="l">Etichetta</td><td><select id="metaFlag" name="metaFlag" id="metaFlag">'+metaFlags+'</select></td></tr><tr><td class="l">Tag</td><td><input type="text" id="metaDesc" name="description" onfocus="window.onkeypress=\'null\'" value="'+meta.description+'" style="width: 100px;"/>&nbsp;<a href="#" onclick="saveMeta(); return false" title="Salva"><img src="images/salva.png" alt="" /></a></td></tr>';
	}
	else if (FC.SELECTEDOBJECT.virtual) {
		$('meta').innerHTML = '<table><tr><td class="l">Nome</td><td>'+meta.name+'</td></tr><tr><td class="l">Dimensione</td><td>'+meta.size+'</td></tr></table>';
	}
	else {
                if(FC.SELECTEDDIRECTORY.path == '/home/e-smith/faxweb/docs/received') {
                $('meta').innerHTML = '<table><tr><td class="l">Fax</td><td>'+meta.numero+'</td></tr><tr><td class="l">Dimensione</td><td>'+meta.size+'</td></tr></table>';

                } else if(FC.SELECTEDDIRECTORY.path == '/home/e-smith/faxweb/docs/sent' || FC.SELECTEDDIRECTORY.path == '/home/e-smith/faxweb/docs/sentm') {
                $('meta').innerHTML = '<table><tr><td class="l">Fax</td><td>'+meta.numero+'</td></tr><tr><td class="l">Dimensione</td><td>'+meta.size+'</td></tr><tr><td class="l">Inviati</td><td>'+meta.inviati+'</td></tr><tr><td class="l">Errori</td><td>'+meta.errori+'</td></tr><tr><td class="l">Ritrasmessi</td><td>'+meta.ritrasmessi+'</td></tr><tr><td class="l">In Attesa</td><td>'+meta.attesa+'</td></tr></table>';  

                } else if(path.match('received')) {
		$('meta').innerHTML = '<table><tr><td class="l">Nome</td><td><input type="text" id="folderMeta" name="folderMeta" value="'+meta.name+'" style="width: 90px;"/>&nbsp;<a href="#" onclick="saveMeta(); return false" title="Salva"><img src="images/salva.png" alt="" /></a></td><tr><td class="l">Fax</td><td>'+meta.numero+'</td></tr><tr><td class="l">Dimensione</td><td>'+meta.size+'</td></tr></table>';

                } else if(path.match('sentm')) {
                $('meta').innerHTML = '<table><tr><td class="l">Nome</td><td><input type="text" id="folderMeta" name="folderMeta" value="'+meta.name+'" style="width: 90px;"/>&nbsp;<a href="#" onclick="saveMeta(); return false" title="Salva"><img src="images/salva.png" alt="" /></a></td></tr><tr><td class="l">Descrizione</td><td>'+meta.desc+'</td></tr><tr><td class="l">Fax</td><td>'+meta.numero+'</td></tr><tr><td class="l">Dimensione</td><td>'+meta.size+'</td></tr><tr><td class="l">Inviati</td><td>'+meta.inviati+'</td></tr><tr><td class="l">Errori</td><td>'+meta.errori+'</td></tr><tr><td class="l">Ritrasmessi</td><td>'+meta.ritrasmessi+'</td></tr><tr><td class="l">In Attesa</td><td>'+meta.attesa+'</td></tr><tr><tr><td class="l">Azioni</td><td>&nbsp;&nbsp;&nbsp;<a href="reportExcel.php?id=' + FC.SELECTEDDIRECTORY.id + '" title="Scarica Report"><img src="images/excel.png" alt="" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="resendError(); return false" title="Reinvia Fax con Errori" ><img src="images/reinvia.png" alt="" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="StopAll(); return false" title="Interrompi l\'invio di tutti i fax" ><img src="images/stop.png" alt="" /></a></td></tr></table>';

                } else if(path.match('sent')) { 
                $('meta').innerHTML = '<table><tr><td class="l">Nome</td><td><input type="text" id="folderMeta" name="folderMeta" value="'+meta.name+'" style="width: 90px;"/>&nbsp;<a href="#" onclick="saveMeta(); return false" title="Salva"><img src="images/salva.png" alt="" /></a></td></tr><tr><td class="l">Fax</td><td>'+meta.numero+'</td></tr><tr><td class="l">Dimensione</td><td>'+meta.size+'</td></tr><tr><td class="l">Inviati</td><td>'+meta.inviati+'</td></tr><tr><td class="l">Errori</td><td>'+meta.errori+'</td></tr><tr><td class="l">Ritrasmessi</td><td>'+meta.ritrasmessi+'</td></tr><tr><td class="l">In Attesa</td><td>'+meta.attesa+'</td></tr></table>';  }

	}
        var frame = document.getElementById("dirList");
        var frame2 = document.getElementById("informationcart");
        var htmlheight = document.body.parentNode.scrollHeight;
        var height = document.getElementById("informationcart2").scrollHeight;
        frame.style.height =  (htmlheight - 120 - height) + "px";
}

reportExcel= function () {
}

resendError=  function () {
        if(confirm('Reinviare tutti i fax con errori ?')) {
        var params = $H({ faxweb: 'ResendError', path: FC.SELECTEDDIRECTORY.id });
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.resendError_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } }); }
}

resendError_handler= function (response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}

StopAll=  function () {
        if(confirm('Interrompere l\'invio di tutti i fax ?')) {
        var params = $H({ faxweb: 'StopAll', path: FC.SELECTEDDIRECTORY.id });
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.StopAll_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } }); } 
}

StopAll_handler= function (response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}

saveMeta = function () {
	if(FC.SELECTEDOBJECT.type == 'directory') {
		FC.SELECTEDOBJECT.rename_handler("", $('folderMeta').value);
		return false;
	}
	var metaFlag = $('metaFlag').options[$('metaFlag').selectedIndex].value;
	var metaDesc = $('metaDesc').value;
	
	FC.SELECTEDOBJECT.flag = metaFlag;
	
	var params = $H({faxweb: 'setMeta', fileid: FC.SELECTEDOBJECT.id, description: metaDesc, flags: metaFlag });
	var ajax = new Ajax.Request(FC.URL, {
		onComplete: function() { $('metaSave').style.display = 'block'; Effect.Fade('metaSave', {duration:3}); },
		onSuccess: FC.SELECTEDOBJECT.refresh.bind(FC.SELECTEDOBJECT),
		method: 'post', 
		parameters: params.toQueryString(), 
		onFailure: function() { showError(ER.ajax); }
	});
}
function showMetaSpinner () { $('meta').innerHTML = '<div style="border-bottom:0;" class="thumbbox"><img src="'+spinnerIcon+'" alt="" /></div>'; }

function parsePath(searchPath) {
       var path = searchPath.split('/');
       var children = $A(FC.SEARCHOBJ.children);
       var object = children.detect( function(value, index) {
		if (value.name == path[1]) return true;
		else return false;
	});
if (path[2]) {
	FC.NEXTPATH = searchPath.replace('/'+path[1], '');
	if(object) {
		if (object.open) {
			FC.SEARCHOBJ.hideActivity();
			FC.SEARCHOBJ = object;
			parsePath(FC.NEXTPATH);
		}
		else {
			FC.SEARCHOBJ.hideActivity();
			object.update();
			FC.SEARCHOBJ = object; 
		}
	}
	else { 
		FC.NEXTPATH = null; 
		return showError(ER.parsePath);
	}
}
else {
	FC.SEARCHOBJ = null;
	FC.NEXTPATH = null;
	object.select();
}
}

function getQuery(variable) {
        var query = window.location.search.substring(1);
        query = query.toQueryParams();

        if(query[variable]) {
	  FC.NEXTPATH = decodeURIComponent(query[variable]);
	  FC.SEARCHOBJ = root;
         }
        return true;
}

function jumpTo(path) {
        path = decodeURI(path);
        FC.SEARCHOBJ = root;
        FC.NEXTPATH = path;
        parsePath(path);
}

function go() { }

function Change(view) {
        inoltra_list.removeAllEntry();
        $('numero').innerHTML = "";
        inoltra_list.getContents(view.value);
}
function PageUp() {
        if(FC.SELECTEDPAGE<FC.SELECTEDTOTPAGE) {
        FC.SELECTEDPAGE++;
        $('pageNumber').innerHTML = FC.SELECTEDPAGE + "/" + FC.SELECTEDTOTPAGE;
        FC.SELECTEDDIRECTORY.update(); }
}
function PageDown() {
        if(FC.SELECTEDPAGE>1) {
        FC.SELECTEDPAGE--;
        $('pageNumber').innerHTML = FC.SELECTEDPAGE + "/" + FC.SELECTEDTOTPAGE;
        FC.SELECTEDDIRECTORY.update(); }
}
function PageStart() {
        if(FC.SELECTEDPAGE!=1) {
        FC.SELECTEDPAGE=1;
        $('pageNumber').innerHTML = FC.SELECTEDPAGE + "/" + FC.SELECTEDTOTPAGE;
        FC.SELECTEDDIRECTORY.update(); }
}
function PageEnd() {
        if(FC.SELECTEDPAGE!=FC.SELECTEDTOTPAGE) {
        FC.SELECTEDPAGE= FC.SELECTEDTOTPAGE;
        $('pageNumber').innerHTML = FC.SELECTEDPAGE + "/" + FC.SELECTEDTOTPAGE;
        FC.SELECTEDDIRECTORY.update(); }
}
function PageCount(path) {
        var params = $H({ faxweb: 'countFax', path: path });
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.PageCount_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } });
}
function PageCount_handler(response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        else { FC.SELECTEDTOTPAGE= jsonObject.bindings[0].page;
        $('pageNumber').innerHTML = FC.SELECTEDPAGE + "/" + FC.SELECTEDTOTPAGE; }
}
function Numbers() {
        var tot= 0;
        for (i=0; i<document.forms['forward'].InoltraElenco.options.length; i++) {
          if (document.forms['forward'].InoltraElenco.options[i].selected) {
          tot = tot + 1;
        } }
        $('numero').innerHTML = "" + tot;
}
function Select() {
        var a=0;
        a=document.forms['send_multifax'].selezione.options.length;
        if(document.forms['send_multifax'].MultiInviaElenco!=null) {
        for (i=0; i<document.forms['send_multifax'].MultiInviaElenco.options.length; i++) {
              if (document.forms['send_multifax'].MultiInviaElenco.options[i].selected) {
                document.forms['send_multifax'].selezione.options[a] = new Option ( document.forms['send_multifax'].MultiInviaElenco.options[i].text, document.forms['send_multifax'].MultiInviaElenco.options[i].value);
                document.forms['send_multifax'].selezione.options[a].selected = true;
        } } }
        var tot= 0;
        for (i=0; i<document.forms['send_multifax'].selezione.options.length; i++) {
         tot = tot + 1;
         }
        $('numero2').innerHTML = "" + tot; 
}
function Select2() {
        var a=0;
        a=document.forms['send_multifax'].selezione.options.length;
        document.forms['send_multifax'].selezione.options[a]= new Option(document.forms['send_multifax'].multifaxnumber.value,"D-" + document.forms['send_multifax'].multifaxnumber.value);
        document.forms['send_multifax'].selezione.options[a].selected= true;
        var tot= 0;
        for (i=0; i<document.forms['send_multifax'].selezione.options.length; i++) {
         tot = tot + 1;
        }
        document.forms['send_multifax'].multifaxnumber.value='';
        $('numero2').innerHTML = "" + tot;
}
function Select3() {
        if(document.forms['send_fax'].InviaElenco!=null) {
        for (i=0; i<document.forms['send_fax'].InviaElenco.options.length; i++) {
                 if (document.forms['send_fax'].InviaElenco.options[i].selected) {
                            var SplitResult = document.forms['send_fax'].InviaElenco.options[i].innerHTML.split("&nbsp;");
                            var Nsplit = SplitResult.length-1;
                            document.forms['send_fax'].cerca.value= SplitResult[Nsplit];          
        } } }
}
function deSelect() {
        for (i=0; i<document.forms['send_multifax'].selezione.options.length; i++) {
        if (document.forms['send_multifax'].selezione.options[i].selected) {
        document.forms['send_multifax'].selezione.options[i]= null; }
        }
        var tot= 0;
        for (i=0; i<document.forms['send_multifax'].selezione.options.length; i++) {
         tot = tot + 1;
        }
        $('numero2').innerHTML = "" + tot; 
}
function ClosePopup () {
        $('InoltraList').style.display= "none";
        document.forms['forward'].address.value='';
        document.forms['forward'].notes.value='';
        for (i=0; i<document.forms['forward'].InoltraElenco.options.length; i++) {
        if (document.forms['forward'].InoltraElenco.options[i].selected) {
        document.forms['forward'].InoltraElenco.options[i].selected= false;
        } }
	$('numero').innerHTML = "";
}
function ClosePopup2 () {
        $('SendList').style.display= "none";
        $('SendList2').style.display= "none";
        if(document.forms['send_fax'].InviaElenco!=null) {
        $('InviaElenco').innerHTML= "";
        for (i=0; i<document.forms['send_fax'].InviaElenco.options.length; i++) {
        if (document.forms['send_fax'].InviaElenco.options[i].selected) {
        document.forms['send_fax'].InviaElenco.options[i].selected= false; }
        }}
        document.forms['send_fax'].s_day.selectedIndex = 0;
        document.forms['send_fax'].k_day.selectedIndex = 0;
        document.forms['send_fax'].cerca.value='';
        document.forms['send_fax'].fax_file.value='';
        document.forms['send_fax'].s_time.value='';
        document.forms['send_fax'].k_time.value='';
}
function ClosePopup3 () {
        $('Address_book').style.display= "none";
        $('fax_number').value= "";
        $('fax_address').value= "";
}
function ClosePopup4 () {
        $('SendList3').style.display= "none";
        if(document.forms['send_multifax'].MultiInviaElenco!=null) {
        $('MultiInviaElenco').innerHTML= "";
        for (i=0; i<document.forms['send_multifax'].MultiInviaElenco.options.length; i++) {
        if (document.forms['send_multifax'].MultiInviaElenco.options[i].selected) {
        document.forms['send_multifax'].MultiInviaElenco.options[i].selected= false; }
        }}
        var length_number = document.forms['send_multifax'].selezione.options.length + 1;
        for (i=0; i< length_number; i++) {
        document.forms['send_multifax'].selezione.options[0]= null; }
        document.forms['send_multifax'].multis_day.selectedIndex = 0;
        document.forms['send_multifax'].multik_day.selectedIndex = 0;
        document.forms['send_multifax'].multicerca.value='';
        document.forms['send_multifax'].rec_file.value='';
        document.forms['send_multifax'].multifax_file.value='';
        document.forms['send_multifax'].multifaxnumber.value='';
        document.forms['send_multifax'].description.value='';
        document.forms['send_multifax'].multis_time.value='';
        document.forms['send_multifax'].multik_time.value='';
        $('numero2').innerHTML = "";
}
function ClosePopup5 () {
        $('SendList4').style.display= "none";
        if(document.forms['send_multifax'].MultiInviaElenco!=null) {
        $('MultiInviaElenco').innerHTML= "";
        for (i=0; i<document.forms['send_multifax'].MultiInviaElenco.options.length; i++) {
        if (document.forms['send_multifax'].MultiInviaElenco.options[i].selected) {
        document.forms['send_multifax'].MultiInviaElenco.options[i].selected= false; }
        } }
        var length_number = document.forms['send_multifax'].selezione.options.length + 1;
        for (i=0; i< length_number; i++) {
        document.forms['send_multifax'].selezione.options[0]= null; }
        document.forms['send_multifax'].multis_day.selectedIndex = 0;
        document.forms['send_multifax'].multik_day.selectedIndex = 0;
        document.forms['send_multifax'].rec_file.value='';
        document.forms['send_multifax'].multicerca.value='';
        document.forms['send_multifax'].multifax_file.value='';
        document.forms['send_multifax'].multifaxnumber.value='';
        document.forms['send_multifax'].description.value='';
        document.forms['send_multifax'].multis_time.value='';
        document.forms['send_multifax'].multik_time.value='';
        $('numero2').innerHTML = "";
}
function ClosePopup6 () {
        $('faxStat').style.display= "none";
}
function ClosePopup7 () {
        $('search_folder').innerHTML= "";
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
        $('advsearcharea').style.display= "none";
}
function OpenPopup () {
        $('SendList').style.display= "inline";
        $('SendList3').style.display= "none";
        $('SendList4').style.display= "none";
}
function OpenPopup2 () {
        $('SendList3').style.display= "inline";
        $('SendList').style.display= "none";
        $('SendList2').style.display= "none";
}
function OpenPopup3() {
        var frame = document.getElementById("dirList");
        var frame2 = document.getElementById("informationcart");
        var htmlheight = document.body.parentNode.scrollHeight;
        if(frame2.style.display== "inline") {
                 frame2.style.display= "none";
                 var height = document.getElementById("informationcart2").scrollHeight;
                 frame.style.height =  (htmlheight - 120 - height) + "px";
        } else {
                 frame2.style.display= "inline";
                 var height = document.getElementById("informationcart2").scrollHeight;
                 frame.style.height =  (htmlheight - 120 - height) + "px";
        }
}
function OpenPopup4 () {
        if(FC.SELECTEDDIRECTORY==null) {
                alert("Selezionare la cartella dove effettuare la ricerca.");
        } else if (FC.SELECTEDDIRECTORY.path== '/home/e-smith/faxweb/docs'){
                alert("Impossibile effettuare una ricerca in Faxweb, selezionare una sottocartella.");
        } else if(FC.SELECTEDDIRECTORY.path!= '/home/e-smith/faxweb/docs') {
        $('SendList').style.display= "none";
        $('SendList2').style.display= "none";
        $('SendList3').style.display= "none";
        $('SendList4').style.display= "none";
        if(FC.SELECTEDDIRECTORY.path.match('received')) {

        $('advsearch_ricevuti').style.display = "inline";
        $('advsearch_inviati').style.display = "none";
        var folder =FC.SELECTEDDIRECTORY.path.split("received");
        $('search_folder').innerHTML= "Ricevuti" + folder[1];

        } else if (FC.SELECTEDDIRECTORY.path.match('sentm')){

        var folder =FC.SELECTEDDIRECTORY.path.split("sentm");
        $('search_folder').innerHTML= "Invii Multipli" + folder[1];
        $('advsearch_ricevuti').style.display = "none";
        $('advsearch_inviati').style.display = "inline";

        } else if (FC.SELECTEDDIRECTORY.path.match('sent')){

        var folder =FC.SELECTEDDIRECTORY.path.split("sent");
        $('search_folder').innerHTML= "Inviati" + folder[1];
        $('advsearch_ricevuti').style.display = "none";
        $('advsearch_inviati').style.display = "inline";
        }
        
        $('advsearcharea').style.display= "inline";
        }
}
function Previous () {
        $('SendList2').style.display= "none";
        $('SendList').style.display= "inline";
}
function Previous2 () {
        $('SendList4').style.display= "none";
        $('SendList3').style.display= "inline";
}
function Send_Fax () {
        if($('SendList2').style.display== "inline") $('SendList2').style.display= "none"
        else $('SendList2').style.display= "inline";
}
function Send_Fax2 () {
        alert('Invio Confermato');
        if(document.forms['send_fax'].InviaElenco!=null) {
        $('InviaElenco').innerHTML= "";
        for (i=0; i<document.forms['send_fax'].InviaElenco.options.length; i++) {
        if (document.forms['send_fax'].InviaElenco.options[i].selected) {
        document.forms['send_fax'].InviaElenco.options[i].selected= false; }
        } }
        document.forms['send_fax'].s_day.selectedIndex = 0;
        document.forms['send_fax'].k_day.selectedIndex = 0;
        document.forms['send_fax'].cerca.value='';
        document.forms['send_fax'].fax_file.value='';
        document.forms['send_fax'].s_time.value='';
        document.forms['send_fax'].k_time.value='';
        $('SendList').style.display= "none";
        $('SendList2').style.display= "none";
        
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}
function Send_Fax3 () {
        $('SendList3').style.display= "none";
        for (i=0; i<document.forms['send_multifax'].selezione.options.length; i++) {
        document.forms['send_multifax'].selezione.options[i].selected= true; }
        $('SendList4').style.display= "inline";
}
function Send_Fax4 () {
        alert('Invio Confermato');
        if(document.forms['send_multifax'].MultiInviaElenco!=null) {
        $('MultiInviaElenco').innerHTML= "";
        for (i=0; i<document.forms['send_multifax'].MultiInviaElenco.options.length; i++) {
        if (document.forms['send_multifax'].MultiInviaElenco.options[i].selected) {
        document.forms['send_multifax'].MultiInviaElenco.options[i].selected= false; }
        } }
        var length_number = document.forms['send_multifax'].selezione.options.length + 1;
        for (i=0; i< length_number; i++) {
        document.forms['send_multifax'].selezione.options[0]= null; }
        document.forms['send_multifax'].multis_day.selectedIndex = 0;
        document.forms['send_multifax'].multik_day.selectedIndex = 0;
        document.forms['send_multifax'].rec_file.value='';
        document.forms['send_multifax'].multicerca.value='';
        document.forms['send_multifax'].multifax_file.value='';
        document.forms['send_multifax'].multifaxnumber.value='';
        document.forms['send_multifax'].description.value='';
        document.forms['send_multifax'].multis_time.value='';
        document.forms['send_multifax'].multik_time.value='';
        $('numero2').innerHTML = "";
        $('SendList3').style.display= "none";
        $('SendList4').style.display= "none";

        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}
function Add_address() {
        $('Address_book').style.display= "none";
        var number_fax = $('fax_number').value;
        var nome = $('fax_address').value;
        if( nome!='' && number_fax!= ''){
        $('fax_number').value= "";
        $('fax_address').value= "";
        var params = $H({ faxweb: 'addAddress',fax: number_fax, address: nome });
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.Add_address_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } });
        } else {
        alert("Numero o Nome non validi");
        } 
}
function Add_address_handler(response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}
function Error_rec_file(number) {
        alert("La lista di destinatari non e' formattata correttamente. Il formato corretto utilizza una riga per ogni destinatario.\n Ogni riga va formattata in questo modo:\n -numero_fax,nome_destinatario.\n\n La riga contenente l'errore e' la seguente:\n" + number + "");
}

function Error_fax_file(type) {
        alert("File inviato non corretto. E' possibile inviare solo PostScript, Tif o PDF!");
}
function Error_upload() {
        alert("Upload del file non riuscito");
}
function Error_desc() {
        alert("Nome Invio Multiplo obbligatorio");
}
function Send_Fax2_handler(response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
}

function send_mail() {
        var email= "";
        var notes= "";
        for (i=0; i<document.forms['forward'].InoltraElenco.options.length; i++) {
        if (document.forms['forward'].InoltraElenco.options[i].selected) {
        a = document.forms['forward'].InoltraElenco.options[i].value + ";";
        document.forms['forward'].InoltraElenco.options[i].selected= false;
        email = email + a ;
        } }
        if(document.forms['forward'].address.value) {
        email = email + document.forms['forward'].address.value;
        }
        notes = document.forms['forward'].notes.value;
        document.forms['forward'].notes.value='';
        document.forms['forward'].address.value='';
        $('InoltraList').style.display= "none";
        $('numero').innerHTML = "";
        var params = $H({ faxweb: 'sendMail',id: FC.SELECTEDOBJECT.id, mail: email , note: notes});
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.send_mail_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } });
}

function send_mail_handler(response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}

function newFolder(){
        if(FC.SELECTEDDIRECTORY==null || FC.SELECTEDOBJECT == null) { alert("Selezionare dove creare la nuova cartella.");
	} else if(FC.SELECTEDOBJECT.type == 'file') c = FC.SELECTEDOBJECT.parentObject;
	else { c = FC.SELECTEDOBJECT;	

	var folderName = 'Nuova Cartella';
	FC.NEXTPATH = '/'+folderName;
	FC.SEARCHOBJ = c;
	
	var params = $H({ faxweb: 'newFolder', name: folderName, path: c.path });
	var ajax = new Ajax.Request(FC.URL,{
		onSuccess: this.newFolder_handler.bind(this),
		method: 'post', 
		parameters: params.toQueryString(), 
		onFailure: function() { showError(ER.ajax); }
	});
        }
}

function newFolder_handler(response) {
	var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
	if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}

function FaxStat() {
        var params = $H({ faxweb: 'faxStat'});
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.faxStat_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } });
}

function faxStat_handler(response) {
        $('faxStat').style.display= "inline";
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        $('modemStat').innerHTML = jsonObject.bindings[0].stat;
}
function FaxStatUpdate() {
        var params = $H({ faxweb: 'faxStat'});
        var ajax = new Ajax.Request(FC.URL, {
        onSuccess: this.faxStatUpdate_handler.bind(this),
        method: 'post',
        parameters: params.toQueryString(),
        onFailure: function() { showError(ER.ajax); } });
}
function faxStatUpdate_handler(response) {
        var json_data = response.responseText;
        eval("var jsonObject = ("+json_data+")");
        if(jsonObject.bindings[0].error) alert(jsonObject.bindings[0].error);
        else { $('modemStat').innerHTML = "";
        $('modemStat').innerHTML = jsonObject.bindings[0].stat; }
}

function Nethesis () {
        window.open('http://www.nethesis.it' );
}
function uploadAuth() {
	if(QFiles.length == 0) return false;
	if(!FC.SELECTEDOBJECT) { return false;}
	
	if(FC.SELECTEDOBJECT.type == 'file') uploadDestination = FC.SELECTEDOBJECT.parentObject;
	else uploadDestination = FC.SELECTEDOBJECT;	

	var params = $H({ faxweb: 'uploadAuth', path: uploadDestination.path });
	var ajax = new Ajax.Request(FC.URL,{ onSuccess: uploadAuth_handler, method: 'post', parameters: params.toQueryString(), onFailure: function() { showError(ER.ajax); } });	
}

function uploadAuth_handler(response) {
	var json_data = response.responseText; 
	eval("var jsonObject = ("+json_data+")");
	var auth = jsonObject.bindings[0];

	if(auth.auth == 'true') {
		sendUpload(auth.sessionid);
	}
	else showError(ER.upload);
}

function sendUpload(sid) {
	var uploadDumb = FC.UPLOADURL + '?'+ sid;
	$('uploadForm').action = uploadDumb;
	$('uploadForm').submit();
	
	$('uploadSubmit').src = uploadCancel;
	$('uploadSubmit').onclick = uploadStop;
	Element.toggle('uploadAdd');
	$('pgfg').style.width = "1px";	
	Effect.Appear('progress');
	window.setTimeout("uploadStatus()", 500);
}

function uploadStatus() {
		var params = $H({ faxweb: 'uploadSmart'});
		var ajax = new Ajax.Request(FC.URL, { onSuccess: uploadStatus_handler, method:'post', parameters: params.toQueryString(), onFailure: function() { showError(ER.ajax); } });		
}

uc = 0;
change = 0;
currentsize = 0;
destination = 0;
pginterval = 2000; 
refresh = 20;
pgwidth = 180;

function uploadStatus_handler(response) {
	var json_data = response.responseText; 
	eval("var jsonObject = ("+json_data+")");
	var progress = jsonObject.bindings[0];

	if(progress.done == 'false') {
		window.setTimeout( "uploadStatus()", 1800);
		if(FC.PG) clearInterval(FC.PG);
		
		var p =  pgwidth * progress.percent;
		$('pgfg').style.width = p + 'px';
		currentsize = p;
		
		var pixels = progress.percentSec * pgwidth;
		change = pixels / refresh;
		destination = currentsize + pixels;
		
		FC.PG = setInterval("updatePgFg()", pginterval/refresh);
		
		$('pgsp').innerHTML = progress.speed;
		$('pgeta').innerHTML = progress.secondsLeft;
		
	}
	else uploadFinish();
}

function updatePgFg() {
	if (currentsize < destination) {
		uc++;		
		currentsize = currentsize + change;
		if(currentsize < pgwidth) {
			$('pgpc').innerHTML = parseInt((currentsize/pgwidth)*100) + "%";
			$('pgfg').style.width = currentsize + 'px';
		}
	}
}

function uploadFinish(stop) {
	change = 0;
	currentsize = 0;
	destination = 0;
	clearInterval(FC.PG);
	if(stop) $('pgpc').innerHTML = "Canceled";
	else $('pgpc').innerHTML = "100%";

	$('uploadSubmit').src = uploadBtn;
	$('uploadSubmit').onclick = uploadAuth;		
	Element.toggle('uploadAdd');		
	$('pgfg').style.width = pgwidth + "px";
	Effect.Fade('progress');
	uploadDestination.update();
	clearQ();
}

function uploadStop() {
	$('uploadiframe').src = "about:blank";
	uploadFinish(true);
}

function unlink() {
	FC.SELECTEDOBJECT ? FC.SELECTEDOBJECT.unlink() : null;
}
function download() {
	FC.SELECTEDOBJECT ? (FC.SELECTEDOBJECT.type == 'file' ? FC.SELECTEDOBJECT.download() : null) : null;
}

function updateAll(obj) {
        if(FC.SELECTEDDIRECTORY!=null) {
	FC.SELECTEDDIRECTORY.update(); }
}
function openAll() {
	var c = FC.SELECTEDOBJECT;
	for (var i = 0; i < c.children.length; i++)
	{
		c.children[i].open = true;
		c.children[i].getFolders();
	}
	return false;
}
function closeAll() {
	root.clearContents(); 
	root.getContents(); 
        root.select();
	return false;
}
function deleteFolder() {
        if(FC.SELECTEDDIRECTORY==null) { alert("Selezionare la cartella da eliminare.");
        } else if (FC.SELECTEDDIRECTORY.path=='/home/e-smith/faxweb/docs/sent' || FC.SELECTEDDIRECTORY.path=='/home/e-smith/faxweb/docs/sentm' || FC.SELECTEDDIRECTORY.path=='/home/e-smith/faxweb/docs/received' || FC.SELECTEDDIRECTORY.path=='/home/e-smith/faxweb/docs' || FC.SELECTEDOBJECT.readonly || FC.SELECTEDOBJECT.virtual) {
           alert("Impossibile eliminare questa Cartella.");
        } else {

        if(confirm('Eliminare la Cartella '+ FC.SELECTEDOBJECT.name+ '?')) {
                var params = $H({ faxweb: 'folderDelete', folder: FC.SELECTEDOBJECT.path });
                var ajax = new Ajax.Request(FC.URL,{
                        onSuccess: deleteFolder_handler,
                        method: 'post',
                        parameters: params.toQueryString(),
                        onFailure: function() { showError(ER.ajax); }
                });
        } }
}
function deleteFolder_handler(response) {
         FC.SELECTEDDIRECTORY.removeChild(FC.SELECTEDDIRECTORY);
         FC.SELECTEDDIRECTORY.parentObject.select();
         if(FC.SELECTEDDIRECTORY!=null) FC.SELECTEDDIRECTORY.update();
}

QFiles = new Array();

var UploadManager = Class.create();
UploadManager.prototype = {
	initialize: function(element) {
		this.uploadQ = $('uploadFiles');
		this.buttons = $('uploadbuttons');
		this.size = 1;
		if(element) {
			this.input = $(element);
			this.id = element;
			this.input.value != '' ? this.addToQ() : null;
		}
		else this.createElement();
		this.input.onchange = this.addToQ.bind(this);
	},
	
	createElement: function() {
		this.id = 'upload'+ getRandom();
		this.input = document.createElement('input');
		this.input.type = 'file';
		this.input.name = 'file[]';
		this.input.size = this.size;
		Element.addClassName(this.input, 'fileupload');
		this.buttons.appendChild(this.input);

	},
	
	addToQ: function() {
		$('uploadQ').style.height = "auto";
		this.QPOS = QFiles.length;
		QFiles[this.QPOS] = this;
		var reg = /(.+(\\|\/))?(.*)/;
		var results = this.input.value.match(reg);
		this.filename = results[3];		
		this.row = document.createElement('tr');
		this.row.id = 'r'+getRandom();				
		this.name = document.createElement('td');
		this.name.innerHTML = '<div class="fileUp">'+this.filename+'</div>';		
		this.del = document.createElement('td');		
		this.link = document.createElement('img');
		this.link.onclick = this.clear.bind(this);
		this.link.src = removeIcon;		
		this.del.appendChild(this.link);
		this.row.appendChild(this.name);
		this.row.appendChild(this.del);
		this.uploadQ.appendChild(this.row);		
		Effect.Appear('uploadSubmit');
		next = new UploadManager();
	},
	
	clear: function() {
		Element.remove(this.row);
		Element.remove(this.input);
		QFiles.splice(this.QPOS, 1);
		if(QFiles.length == 0) {
			 Effect.Fade('uploadSubmit');
			 $('uploadQ').style.height = "28px";
		}
	},
	
	remove: function() {
		Element.remove(this.row);
		Element.remove(this.input);
	}
};

function getRandom() { return Math.round(Math.random()*1000); }
function clearQ() {
	for(var i=0;i < QFiles.length; i++){ QFiles[i].remove(); }

	Effect.Fade('uploadSubmit');
	$('uploadQ').style.height = "28px";
	QFiles = new Array();
}

var Cart = Class.create();
Cart.prototype = {
	initialize: function() {
		this.element = $('cart');
		this.children = new Array();
		this.confirm = $('emailconfirm');
		Droppables.add('cart', { accept: 'file', hoverclass: 'hover', onDrop: this.add.bind(this) });
	},
	
	add: function (element) {
		var name = element.object.name;
		var fileid = element.object.id;
		for(var i=0; i < this.children.length; i++){
			if(fileid == this.children[i]) { return false; }
		}

		row = document.createElement('div');
		row.id = 'c'+fileid;
		row.innerHTML = '<div>'+name+'</div>';
		row.innerHTML += '<a href="#" onclick="cart.remove(\''+fileid+'\'); return false" class="remove"></a>';

		this.element.appendChild(row);
		this.children[this.children.length] = fileid;
		
	},
	addSpecial: function (fid, fname) {
		var e = { object: {name : fname, id: fid} };
		cart.add(e);
	},
			
	remove: function (fid) {
		for(var i=0; i < this.children.length; i++){
			if(fid == this.children[i]) { this.children.splice(i,1); Element.remove('c'+fid); break; }
		}
	},
	
	download: function() {
		if(this.children.length == 0 && FC.SELECTEDOBJECT == null) return false;
		if(this.children.length == 0 && FC.SELECTEDOBJECT) { FC.SELECTEDOBJECT.download(); return false; }
		var cartIDs = '';
		
		for(var i=0; i < this.children.length; i++) {
			if(this.children[i] != ''){
				cartIDs += this.children[i];
				if(i != this.children.length-1) cartIDs += ',';
			}
		}
		for(var i=0;i< this.children.length; i++){ 
			Element.remove('c'+this.children[i]);
		}
		this.children = new Array();

		if($('emailFormTo').value != '' && $('emailFormTo').value != 'Type email address') 
			this.email(cartIDs);
		else
			location.href = FC.URL+'?faxweb=getFilePackage&fileid=' + cartIDs;
			
	},
	
	email: function(cartIDs) {
		var params = $H({
			faxweb: 'emailFilePackage', 
			to: $('emailFormTo').value, 
			from: $('emailFormFrom').value, 
			message: $('emailFormMessage').value,
			fileid: cartIDs
		});	
		var ajax = new Ajax.Request(FC.URL,{
			onSuccess: this.email_handler.bind(this),
			method: 'get', 
			parameters: params.toQueryString()
		});
	},
	
	email_handler: function() {
		this.hideEmail();
		Effect.Appear(this.confirm);
		setTimeout("Effect.Fade('emailconfirm');", 2000);
	},
	showEmail: function() {
		Effect.Appear('emailform');
	},
	hideEmail: function() {
		Effect.Fade('emailform');
	}
	
};

var ER = {
	auth: 'You don\'t have authorization to use this app. Please try <a href="login.htm">logging in</a> again',
	ajax: 'Unable to make a connection to the server',
	upload: 'Can\'t upload to this folder. You may not have write privileges',
	download: 'Your download cart is empty',
	parsePath: 'The file you are looking for is not there'
};

showError = function(text) {
	$('error').innerHTML = "<img src=\"/images/icons/exclamation.png\" /><p>" + text + "</p><a href=\"#\" class=\"close\" onclick=\"Effect.toggle('error', 'appear'); return false\" />Chiudi</a>";
	Effect.Appear('error');
	return false;
}

showLogin = function() {
	$('login').style.display = "block";
}

function userLogin(){
	var params = $H({ faxweb: 'userLogin', username: $('username').value, password: $('password').value });
	var ajax = new Ajax.Request(FC.URL,{
		onSuccess: userLogin_handler,
		method: 'post', 
		parameters: params.toQueryString()
	});
	return false;
}

function userLogin_handler(response){      
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	var status = jsonObject.bindings[0];
	if (status.login == 'true'){ 
		root.getContents(); 
		$('login').style.display="none";
	}
	else if ($('password').value != '') $('warning').style.display="block";
                        
}

function submitenter(myfield,e) {
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;
	
	if (keycode == 13) {
	   userLogin();
	   return false;
	  }
	else return true;
}

function Resize(){
	var htmlheight = document.body.parentNode.scrollHeight;
	$('fileList').style.height = htmlheight - 120 + "px";
	$('Left_content').style.height = htmlheight - 120 + "px";
}

search = null;
cart = null;
root = null;
file_list = null;
inoltra_list= null;

windowLoader = function () {
        Resize();

	root = new Directory('', '', false, $('dirList'));
	file_list = new Directory('', '', false, $('fileList'), false, 'write');
	inoltra_list = new ForwardList($('InoltraElenco'));

	root.open = true;
	root.getContents();
	getQuery('path');
	
	new UploadManager('fileUpload');
	cart = new Cart('cart');
	
	setInterval("updateAll(root)", 300000);
	search = new Search('searcharea');
	
	var ajax = new Ajax.Request('faxweb.php', {onSuccess: userLogin_handler_check, method: 'post', parameters: 'faxweb=checkLogin'});
}

function userLogin_handler_check(response){      
	var json_data = response.responseText;
	eval("var jsonObject = ("+json_data+")");
	var status = jsonObject.bindings[0];
	if(status.login != 'true'){		
		document.location = "index.php";
	}
}

window.onload = windowLoader;
