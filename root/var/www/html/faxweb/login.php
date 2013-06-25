<?php
session_start();

class NethServiceAuth
{
    function NethServiceAuth()
    {
        $this->_logged_in = false;

        $this->_filter_recv = true;
        $this->_filter_sent = true;
        $this->_admin_recv  = false;
        $this->_admin_sent  = false;

        if ($faxweb['filter_recv'] == 'false') $this->_filter_recv = false;
        if ($faxweb['filter_sent'] == 'false') $this->_filter_sent = false;
    }    

    function Authenticate($username, $password)
    {

        $this->_logged_in = false;
        system("/var/www/html/faxweb/auth.pl $username $password", $ret);
        if ($ret === 0) $this->_logged_in = true;
    }

    function isLoggedIn() { return $this->_logged_in; }
};

?>
