<?php
/*
# ================= DO NOT MODIFY THIS FILE =================
# 
# Manual changes will be lost when this file is regenerated.
#
# Please read the developer's guide, which is available
# at https://dev.nethesis.it/projects/nethserver/wiki/NethServer
# original work from http://www.contribs.org/development/
#
# Copyright (C) 2013 Nethesis S.r.l. 
# http://www.nethesis.it - support@nethesis.it
# 
*/


$GLOBALS['tablePrefix'] = "faxweb_";
$GLOBALS['base_ldap'] =  'dc=directory,dc=nh';
$GLOBALS['phonebook'] = 'none';


$database = mysql_connect('localhost','faxuser','faxpass') or die("Database error check conf.inc.php");
mysql_select_db('faxdb', $database);



// global variables
$imageTypes = "/application\/pdf|image\/|application\/postscript|image\/tiff/";
$dateFormat = "%d-%m-%Y %H:%i ";
$passwordKey = "d6b3cc24dcf628fe6cd8df159983ea07";
$ghostScript="/usr/bin/gs";
$deleted=false;

// paths
$rootPath    = "/var/lib/nethserver/fax";
$dochome = "/var/lib/nethserver/fax/docs";
$defaultFileStore = "/var/lib/nethserver/fax/docs";
$uploadDir   = "/var/www/html/faxweb";

#Configurazione generata da template
$faxweb['adminsent']=array('admin');
$faxweb['adminrcv']=array('admin');
$faxweb['filtersent']=true;
$faxweb['filterrcv']=true;
$faxweb['filterdevice']=false;
$faxweb['faxmanager']=array("admin","admin","alessandrog","andrea","cristian","davide","filippo","massimop","stefanof","support");
$limit=25;
$notify=false;


?>
