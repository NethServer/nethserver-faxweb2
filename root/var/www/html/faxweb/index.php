<?php
include_once("conf.inc.php");
include_once("login.php");
    
    if(isset($_SERVER['ORIG_PATH_TRANSLATED']) and $_SERVER['ORIG_PATH_TRANSLATED'] != ''){
        $rootpath =$_SERVER['ORIG_PATH_TRANSLATED'];
    }else if(isset($_SERVER['PATH_TRANSLATED']) and $_SERVER['PATH_TRANSLATED'] != ''){
        $rootpath = $_SERVER['PATH_TRANSLATED'];
    }else{
        $rootpath = $_SERVER['SCRIPT_FILENAME'];
    }
        
    $rootpath = str_replace("index.php","",$rootpath);
    
    if(substr($rootpath,0,1) == '/'){
            $path = explode("/","$rootpath"); // "/"
    }else if(substr($rootpath,2,1) == "\\"){
            $path = explode("\\","$rootpath"); // "\"
    }else if(substr($rootpath,2,2) == "\\\\"){
            $path = explode("\\\\","$rootpath"); // "\\"
    }else{
            $path = explode("/","$rootpath");
    }
    
    $rootpath = '';
    
    for($i=0;$i<count($path)-1;$i++){
	$rootpath .= "$path[$i]/";
    }
    
    if(substr($rootpath,-1,1) == "/"){
	$rootpath = substr($rootpath,0,strlen($rootpath)-1);
    }

    if(!file_exists("$rootpath/conf.inc.php")){

	header("Location: install/index.php");
	exit;
    }
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="shortcut icon" href="images/favicon.ico" >
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>FaxWeb 2.0</title>
	<script src="js/prototype.js" type="text/javascript"></script>
	<script src="js/effects.js" type="text/javascript"></script>
	<script type="text/javascript">
		var url = 'faxweb.php';
		var mainsite = 'faxweb_html.php';
		var ajax = new Ajax.Request(url, {onSuccess: userLogin_handler, method: 'post', parameters: 'faxweb=checkLogin'});

                function Nethesis () {
                                       window.open('http://www.nethesis.it' );
                }
		function userLogin(){
			$('warning').style.display="none";
                        $('warning2').style.display="none";
			$('waiting').style.display="block";
			var params = $H({ faxweb: 'userLogin', username: $('username').value, password: $('password').value });
			
			var ajax = new Ajax.Request(url,{
				onSuccess: userLogin_handler,
				method: 'post', 
				parameters: params.toQueryString()
			});
			
		}
		function userLogin_handler(response){      
			var json_data = response.responseText;
			eval("var jsonObject = ("+json_data+")");
			var status = jsonObject.bindings[0];
			if(status.login == 'true') {
			        $('waiting').style.display="none";
				document.location = mainsite;
                        } else if(status.login == 'faxmanager') {
               			$('waiting').style.display="none";
				$('warning').style.display="none";
                                $('warning2').style.display="block";
                                Effect.Shake('login');
			} else {
	         		$('waiting').style.display="none";
                                $('warning2').style.display="none";
				$('warning').style.display="block";
				Effect.Shake('login');
			}
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
		window.onload = function() {
			Field.activate('username');
		}
	</script>
	<style type="text/css" media="all">
		body { margin:0; padding:0; font-size:10pt; background:white /*url(images/headerbar.png) left top repeat-x*/; font-family:Helvetica, Arial, sans-serif;}
		p { margin:0; padding:0;}
		h1 { color:white; font-size:24pt; margin:60px 0; }
		h2 { color:#606060; font-size:12pt; margin:0; }
		hr {border:0; height:1px; margin:20px 0; background:#7ecc1b; }
		a {color:#316ac5; font-weight:bold; text-decoration:none;}
		#container { width:400px; position:relative; left:50%; margin-left:-200px; padding-bottom:200px; }
		#header { height:121px; margin-bottom:20px;}
		#blurb {float:left; color:#606060; margin-top:5px; font-size:11pt; line-height:16pt;}
		#body {float:left; min-height:300px; margin-top:15px; width:100%; padding:12px; background:white url(images/body-grad.png) left top repeat-x;}
		#body ul { margin:0 0 0 10px; padding:10px; }
		#body li { padding:3px 0; font-size:14pt; color:#707b65; }
		.left {float:left; width:320px; padding:0 5px 5px; margin-left:10px; }
		.right { float:right; width:254px; }
		#footer { float:left; width:100%; margin-top:100px; text-align:center;}
		#footer p {color:#bbb; }

		td {font-size:9pt;  color:#333;}
		td.note {
			color:#999;
			padding-bottom:15px;
			padding-left:15px;
		}
		td h2 {
			
			margin-top:20px;
		}
		.label {
			text-align:right;
			vertical-align:top;
			padding-top:3px;
			 
		}
                table.login {
                        width: 100%;
                        border: none;
                        font-family:Tahoma, Verdana, Arial,Helvetica ;
                        text-decoration:none;
                }
                td.login {
                        border: white;
                        border-bottom: white solid 4px;
                        font-family:Tahoma, Verdana, Arial,Helvetica ;
                        text-decoration:none;
                }
		input.border {
			font-family:helvetica, arial, sans-serif;
			font-size:10pt;
			padding:3px;
			background:white;
			margin:0 10px 5px;
			border-left:1px solid #83a5c7; 
			border-top:1px solid #83a5c7; 
			border-bottom:1px solid #d3e1ee;  
			border-right:1px solid #d3e1ee; 
		}
		.warning {
			border:1px solid white;
			background: #316ac5;
			color: white;
			padding:10px;
			margin:0 0 15px 0;
		}
		#installform { margin:0 20px; }
		#installform td img {margin-bottom:5px; }
		.submit {
			border:auto;
		}
	</style>
</head>
<body>
<div id="container">
	<div id="header">
	</div>
	<div id="body">
         <form onsubmit="userLogin(); return false" action="." method="get" >
          <table style="margin:20px 0 0 70px;">
	   <tr>
            <td>Username</td> <td><input size="20" class="border" type="text" name="username" value="" id="username" /></td>
	   </tr>
           <tr>
            <td></td> <td></td>
	   </tr>
	   <tr>
            <td>Password</td> <td><input size="20" class="border" type="password" name="password" onkeypress="return submitenter(this,event)" value="" id="password" /></td>
	   </tr>
          </table> 
         </form>
         <input type="button" onclick="userLogin()" style="margin-left:145px" name="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Login&nbsp;&nbsp;&nbsp;&nbsp; " />
	 <br>
	 <br>
	 <div class="warning" id="waiting" style="display:none; text-align: center;"> Verifica login in corso ...... </div>
	 <div class="warning" id="warning" style="display:none; text-align: center;"> Username e/o Password Errati.  </div>
         <div class="warning" id="warning2" style="display:none; text-align: center;"> Accesso Negato. Utente non in Faxmanager.  </div>
         <div id="logo" >
             <table class="login">
                <tr><td class="login" align="left"><font face="arial" color="white" size="30"><b>FaxWeb</b>2.0</font></td></tr>
                <tr><td align="right" valign="top"><font face="arial" size="30"><a href="#" title="Faxweb 2.0 @ 2007 Nethesis srl" onclick="Nethesis();" style="cursor: pointer;">nethesis</font>srl</a></td></tr>
             </table>
         </div>
	</div>
</div>
</body>
</html>
